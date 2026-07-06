<?php

namespace App\Http\Requests\Admin\RoleRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRolePermissionsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'permissions'   => ['present', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }
}
