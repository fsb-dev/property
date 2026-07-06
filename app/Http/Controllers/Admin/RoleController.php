<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest\StoreRoleRequest;
use App\Http\Requests\Admin\RoleRequest\UpdateRolePermissionsRequest;
use App\Services\RoleService;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private RoleService $service) {}

    public function store(StoreRoleRequest $request)
    {
        $this->service->createRole($request->validated());

        return redirect()->route('admin.users.index')
            ->with('toast', ['type' => 'success', 'message' => 'Role created successfully.']);
    }

    public function updatePermissions(UpdateRolePermissionsRequest $request, Role $role)
    {
        $this->service->updateRolePermissions($role, $request->validated()['permissions']);

        return redirect()->route('admin.users.index')
            ->with('toast', ['type' => 'success', 'message' => "Permissions updated for {$role->name}."]);
    }
}
