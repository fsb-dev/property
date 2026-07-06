<?php

namespace App\Http\Requests\Admin\UserRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone'         => ['nullable', 'string', 'max:30'],
            'department'    => ['nullable', 'string', 'max:100'],
            'password'      => ['nullable', 'string', 'min:8', 'confirmed'],
            'role'          => ['required', 'string', Rule::exists('roles', 'name')],
            'avatar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];
    }
}
