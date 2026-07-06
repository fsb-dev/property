<?php

namespace App\Services;

use App\Models\User;
use App\Support\RoleDisplay;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    use RoleDisplay;

    public function __construct(private ActivityLogger $logger) {}

    /** All permissions grouped by resource (the second word of "view clients" -> "clients"). */
    public function permissionGroups(): array
    {
        return Permission::orderBy('name')->get()
            ->groupBy(fn (Permission $p) => explode(' ', $p->name)[1] ?? 'general')
            ->map(fn ($perms, $group) => [
                'group' => str($group)->replace('_', ' ')->title()->toString(),
                'permissions' => $perms->map(fn (Permission $p) => [
                    'value' => $p->name,
                    'label' => str(explode(' ', $p->name)[0])->title()->toString(),
                ])->values(),
            ])
            ->values()
            ->toArray();
    }

    /** All roles with their id, label and currently-assigned permission names — used to prefill the Role Permissions dialog. */
    public function rolesWithPermissions(): array
    {
        return Role::with('permissions')->orderBy('name')->get()
            ->map(fn (Role $r) => [
                'id'          => $r->id,
                'value'       => $r->name,
                'label'       => $this->roleLabel($r->name),
                'permissions' => $r->name === 'super_admin'
                    ? Permission::pluck('name')
                    : $r->permissions->pluck('name'),
            ])
            ->values()
            ->toArray();
    }

    /** All users with id/name/email and their current direct (non-role) permission names — used to prefill Assign Permissions dialog. */
    public function usersWithDirectPermissions(): array
    {
        return User::with('permissions')->orderBy('name')->get()
            ->map(fn (User $u) => [
                'id'          => $u->id,
                'name'        => $u->name,
                'email'       => $u->email,
                'permissions' => $u->permissions->pluck('name'),
            ])
            ->values()
            ->toArray();
    }

    public function createRole(array $data): Role
    {
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        $this->logger->log("New role created: {$this->roleLabel($role->name)}");

        return $role;
    }

    public function updateRolePermissions(Role $role, array $permissions): Role
    {
        $role->syncPermissions($permissions);

        $this->logger->log("Permissions updated for role: {$this->roleLabel($role->name)}");

        return $role;
    }

    public function assignUserPermissions(User $user, array $permissions): User
    {
        $user->syncPermissions($permissions);

        $this->logger->log("Direct permissions updated for user: {$user->name}");

        return $user;
    }

    /** Role x Permission access matrix, for the Access Reports export. */
    public function accessReportCsv(): string
    {
        $roles = Role::with(['permissions', 'users'])->orderBy('name')->get();

        $csv = "Role,User Count,Permission Count,Permissions\n";
        foreach ($roles as $role) {
            $permissions = $role->name === 'super_admin'
                ? Permission::pluck('name')
                : $role->permissions->pluck('name');

            $csv .= implode(',', array_map(fn ($v) => '"'.str_replace('"', '""', $v).'"', [
                $this->roleLabel($role->name),
                $role->users->count(),
                $permissions->count(),
                $permissions->implode('; '),
            ]))."\n";
        }

        return $csv;
    }
}
