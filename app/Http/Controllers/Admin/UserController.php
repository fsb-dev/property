<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest\AssignPermissionsRequest;
use App\Http\Requests\Admin\UserRequest\StoreUserRequest;
use App\Http\Requests\Admin\UserRequest\UpdateUserRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct(
        private UserService $service,
        private RoleService $roleService,
        private ActivityLogger $logger,
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Admin/Users/Index', [
            'users'                     => $this->service->paginate($request),
            'stats'                     => $this->service->stats(),
            'enums'                     => $this->service->enums(),
            'roleDistribution'          => $this->service->roleDistribution(),
            'accessSummary'             => $this->service->accessSummary(),
            'rolesOverview'             => $this->service->rolesOverview(),
            'departmentBreakdown'       => $this->service->departmentBreakdown(),
            'recentActivity'            => $this->logger->recent(),
            'permissionGroups'          => $this->roleService->permissionGroups(),
            'rolesWithPermissions'      => $this->roleService->rolesWithPermissions(),
            'usersWithDirectPermissions' => $this->roleService->usersWithDirectPermissions(),
            'filters'                   => $request->only('search', 'role', 'verified'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            'enums' => $this->service->enums(),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->route('admin.users.index')
            ->with('toast', ['type' => 'success', 'message' => 'User created successfully.']);
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user'  => $this->service->forEdit($user),
            'enums' => $this->service->enums(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->service->update($user, $request->validated());

        return redirect()->route('admin.users.index')
            ->with('toast', ['type' => 'success', 'message' => 'User updated successfully.']);
    }

    public function updatePermissions(AssignPermissionsRequest $request, User $user)
    {
        $this->roleService->assignUserPermissions($user, $request->validated()['permissions']);

        return redirect()->route('admin.users.index')
            ->with('toast', ['type' => 'success', 'message' => "Permissions updated for {$user->name}."]);
    }

    public function destroy(User $user)
    {
        $error = $this->service->delete($user);

        if ($error) {
            return redirect()->route('admin.users.index')
                ->with('toast', ['type' => 'error', 'message' => $error]);
        }

        return redirect()->route('admin.users.index')
            ->with('toast', ['type' => 'success', 'message' => 'User removed successfully.']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $result = $this->service->importCsv($request->file('file'));

        return redirect()->route('admin.users.index')
            ->with('toast', [
                'type'    => $result['created'] > 0 ? 'success' : 'error',
                'message' => "{$result['created']} user(s) created, {$result['skipped']} skipped.",
            ]);
    }

    public function accessReport()
    {
        return new Response($this->roleService->accessReportCsv(), 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="access-report-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    public function export()
    {
        return new Response($this->service->exportCsv(), 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user()->load('roles.permissions');

        $recentActivity = \App\Models\ActivityLog::where('causer_id', $user->id)
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($log) => [
                'description' => $log->description,
                'time'        => $log->created_at->diffForHumans(),
                'date'        => $log->created_at->format('d M Y, H:i'),
            ])
            ->toArray();

        $permissions = $user->getAllPermissions()->pluck('name')->sort()->values();

        // Group permissions by prefix (e.g. "view projects" → "projects")
        $permissionGroups = $permissions->groupBy(fn ($p) => explode(' ', $p)[1] ?? $p)
            ->map(fn ($perms, $group) => [
                'group'  => ucfirst($group),
                'items'  => $perms->map(fn ($p) => ucfirst(explode(' ', $p)[0] ?? $p))->values(),
            ])
            ->values();

        return Inertia::render('Admin/Profile/Show', [
            'profileUser' => [
                'id'                 => $user->id,
                'name'               => $user->name,
                'email'              => $user->email,
                'phone'              => $user->phone,
                'department'         => $user->department,
                'role'               => $user->roles->first()?->name ?? $user->role,
                'avatar_url'         => $user->avatar_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($user->avatar_path) : null,
                'email_verified_at'  => $user->email_verified_at,
                'created_at'         => $user->created_at,
                'updated_at'         => $user->updated_at,
                'tenant_id'          => $user->tenant_id,
                'permissions_count'  => $permissions->count(),
                'permission_groups'  => $permissionGroups,
                'recent_activity'    => $recentActivity,
                'activity_count'     => \App\Models\ActivityLog::where('causer_id', $user->id)->count(),
            ],
        ]);
    }
}
