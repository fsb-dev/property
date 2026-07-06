<?php

namespace App\Services;

use App\Models\User;
use App\Support\RoleDisplay;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserService
{
    use RoleDisplay;

    public function __construct(private ActivityLogger $logger) {}

    public function paginate(Request $request): LengthAwarePaginator
    {
        return User::query()
            ->with('roles')
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            }))
            ->when($request->role, fn ($q) => $q->whereHas('roles', fn ($q) => $q->where('name', $request->role)))
            ->when($request->verified === 'verified', fn ($q) => $q->whereNotNull('email_verified_at'))
            ->when($request->verified === 'unverified', fn ($q) => $q->whereNull('email_verified_at'))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (User $u) => $this->row($u));
    }

    private function row(User $u): array
    {
        $roleName = $u->roles->first()?->name ?? $u->role;

        return [
            'id'         => $u->id,
            'name'       => $u->name,
            'email'      => $u->email,
            'phone'      => $u->phone,
            'department' => $u->department ?: 'Unassigned',
            'role'       => $roleName,
            'role_label' => $this->roleLabel($roleName),
            'role_color' => $this->roleColor($roleName),
            'avatar'     => $u->avatar_path ? Storage::disk('public')->url($u->avatar_path) : null,
            'verified'   => (bool) $u->email_verified_at,
            'created_at' => $u->created_at->format('d M Y'),
        ];
    }

    public function stats(): array
    {
        $adminRoles = ['super_admin', 'company_admin'];

        return [
            'total'          => User::count(),
            'verified'       => User::whereNotNull('email_verified_at')->count(),
            'unverified'     => User::whereNull('email_verified_at')->count(),
            'total_roles'    => Role::count(),
            'departments'    => User::whereNotNull('department')->where('department', '!=', '')->distinct('department')->count('department'),
            'admins'         => User::whereHas('roles', fn ($q) => $q->whereIn('name', $adminRoles))->count(),
            'new_this_month' => User::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count(),
        ];
    }

    public function enums(): array
    {
        return [
            'roles' => Role::orderBy('name')->get()->map(fn (Role $r) => [
                'value' => $r->name,
                'label' => $this->roleLabel($r->name),
            ]),
        ];
    }

    public function roleDistribution(): array
    {
        $total = max(User::count(), 1);

        return Role::withCount('users')->orderByDesc('users_count')->get()
            ->filter(fn (Role $r) => $r->users_count > 0)
            ->map(fn (Role $r) => [
                'name'  => $this->roleLabel($r->name),
                'val'   => $r->users_count,
                'pct'   => round($r->users_count / $total * 100).'%',
                'color' => $this->roleColor($r->name)['color'],
            ])
            ->values()
            ->toArray();
    }

    public function accessSummary(): array
    {
        $buckets = [
            'Full Access'    => ['count' => 0, 'color' => '#5B3DF5'],
            'Manage Access'  => ['count' => 0, 'color' => '#3B82F6'],
            'View Access'    => ['count' => 0, 'color' => '#22C55E'],
            'Limited Access' => ['count' => 0, 'color' => '#F59E0B'],
        ];

        $totalPermissions = max(\Spatie\Permission\Models\Permission::count(), 1);

        User::with('roles.permissions')->get()->each(function (User $u) use (&$buckets, $totalPermissions) {
            if ($u->hasRole('super_admin')) {
                $count = $totalPermissions;
            } else {
                $count = $u->roles->flatMap->permissions->pluck('id')->unique()->count();
            }

            $ratio = $count / $totalPermissions;
            $bucket = match (true) {
                $ratio >= 0.75 => 'Full Access',
                $ratio >= 0.4  => 'Manage Access',
                $ratio > 0     => 'View Access',
                default        => 'Limited Access',
            };

            $buckets[$bucket]['count']++;
        });

        $total = max(User::count(), 1);

        return collect($buckets)->map(fn ($b, $name) => [
            'name'  => $name,
            'val'   => $b['count'],
            'pct'   => round($b['count'] / $total * 100).'%',
            'color' => $b['color'],
        ])->values()->toArray();
    }

    public function rolesOverview(): array
    {
        return Role::withCount(['users', 'permissions'])->orderBy('name')->get()
            ->map(fn (Role $r) => [
                'name'   => $this->roleLabel($r->name),
                'access' => $r->name === 'super_admin'
                    ? 'All permissions'
                    : $r->permissions_count.' permission'.($r->permissions_count === 1 ? '' : 's'),
                'count'  => $r->users_count.' User'.($r->users_count === 1 ? '' : 's'),
                'color'  => $this->roleColor($r->name)['color'],
                'bg'     => $this->roleColor($r->name)['bg'],
            ])
            ->values()
            ->toArray();
    }

    private const DEPARTMENT_COLORS = ['#5B3DF5', '#3B82F6', '#22C55E', '#F59E0B', '#0EA5E9', '#EC4899', '#0D9488', '#94A3B8'];

    public function departmentBreakdown(): array
    {
        $total = max(User::count(), 1);

        return User::query()
            ->selectRaw("COALESCE(NULLIF(department, ''), 'Unassigned') as department, COUNT(*) as aggregate")
            ->groupBy('department')
            ->orderByDesc('aggregate')
            ->get()
            ->values()
            ->map(fn ($row, $i) => [
                'name'  => $row->department,
                'count' => $row->aggregate,
                'pct'   => round($row->aggregate / $total * 100).'%',
                'color' => self::DEPARTMENT_COLORS[$i % count(self::DEPARTMENT_COLORS)],
            ])
            ->toArray();
    }

    public function forEdit(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'department' => $user->department,
            'role'       => $user->roles->first()?->name ?? $user->role,
            'avatar'     => $user->avatar_path ? Storage::disk('public')->url($user->avatar_path) : null,
        ];
    }

    public function create(array $data): User
    {
        $avatar = $data['avatar'] ?? null;
        $data   = collect($data)->except(['avatar'])->toArray();

        $data['tenant_id'] = auth()->user()?->tenant_id;

        $user = User::create($data);
        $this->syncRole($user, $data['role']);

        if ($avatar) {
            $user->update(['avatar_path' => $avatar->store('avatars', 'public')]);
        }

        $this->logger->log("New user added: {$user->name} joined as {$this->roleLabel($data['role'])}");

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $avatar       = $data['avatar'] ?? null;
        $removeAvatar = $data['remove_avatar'] ?? false;
        $data         = collect($data)->except(['avatar', 'remove_avatar'])->toArray();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $previousRole = $user->roles->first()?->name ?? $user->role;

        $user->update($data);
        $this->syncRole($user, $data['role']);

        if ($avatar) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->update(['avatar_path' => $avatar->store('avatars', 'public')]);
        } elseif ($removeAvatar && $user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        if ($previousRole !== $data['role']) {
            $this->logger->log("Role updated: {$user->name} is now {$this->roleLabel($data['role'])}");
        } else {
            $this->logger->log("User updated: {$user->name}'s details were changed");
        }

        return $user->fresh();
    }

    public function delete(User $user): ?string
    {
        if ($user->id === auth()->id()) {
            return 'You cannot delete your own account.';
        }

        if ($user->hasRole('super_admin') && User::role('super_admin')->count() <= 1) {
            return 'At least one Super Admin must remain.';
        }

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $name = $user->name;
        $user->delete();

        $this->logger->log("User removed: {$name}");

        return null;
    }

    public function exportCsv(): string
    {
        $rows = User::with('roles')->get();

        $csv = "Name,Email,Phone,Department,Role,Verified,Joined\n";
        foreach ($rows as $u) {
            $csv .= implode(',', array_map(fn ($v) => '"'.str_replace('"', '""', $v).'"', [
                $u->name,
                $u->email,
                $u->phone ?? '',
                $u->department ?? '',
                $this->roleLabel($u->roles->first()?->name ?? $u->role),
                $u->email_verified_at ? 'Yes' : 'No',
                $u->created_at->format('d M Y'),
            ]))."\n";
        }

        return $csv;
    }

    /**
     * Bulk-import users from an uploaded CSV (columns: name, email, phone, role).
     * Rows with a missing name/email, an unknown role, or an already-used email are skipped.
     *
     * @return array{created: int, skipped: int}
     */
    public function importCsv(\Illuminate\Http\UploadedFile $file): array
    {
        $rows = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_map(fn ($h) => strtolower(trim($h)), array_shift($rows) ?? []);

        $validRoles = Role::pluck('name')->all();
        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if (count($row) < count($header)) {
                $skipped++;
                continue;
            }

            $entry = array_combine($header, $row);
            $name  = trim($entry['name'] ?? '');
            $email = trim($entry['email'] ?? '');
            $role  = trim($entry['role'] ?? '');

            if (!$name || !$email || !in_array($role, $validRoles, true) || User::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            $this->create([
                'name'       => $name,
                'email'      => $email,
                'phone'      => trim($entry['phone'] ?? '') ?: null,
                'department' => trim($entry['department'] ?? '') ?: null,
                'password'   => \Illuminate\Support\Str::random(12),
                'role'       => $role,
            ]);
            $created++;
        }

        $this->logger->log("Bulk import: {$created} user(s) created, {$skipped} skipped");

        return ['created' => $created, 'skipped' => $skipped];
    }

    private function syncRole(User $user, ?string $role): void
    {
        if (!$role) {
            return;
        }

        $user->syncRoles([$role]);
        $user->update(['role' => $role]);
    }
}
