<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'  => ['required', 'exists:projects,id'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'update_date' => ['required', 'date'],
            'progress'      => ['required', 'numeric', 'min:0', 'max:100'],
            'time_progress' => ['required', 'numeric', 'min:0', 'max:100'],
            'budget_total'  => ['required', 'numeric', 'min:0'],
            'budget_used'   => ['required', 'numeric', 'min:0', 'lte:budget_total'],
            'photo'         => ['nullable', 'image', 'max:5120'],
        ];
    }
}
