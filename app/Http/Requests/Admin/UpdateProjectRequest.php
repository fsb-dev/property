<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProjectCategory;
use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access controlled by route middleware
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'type'          => ['required', Rule::enum(ProjectType::class)],
            'category'      => ['nullable', Rule::enum(ProjectCategory::class)],
            'status'        => ['required', Rule::enum(ProjectStatus::class)],
            'location'      => ['nullable', 'string', 'max:255'],
            'address'       => ['nullable', 'string', 'max:500'],
            'description'   => ['nullable', 'string'],
            'total_floors'  => ['nullable', 'integer', 'min:1', 'max:200'],
            'total_units'   => ['nullable', 'integer', 'min:1', 'max:5000'],
            'handover_date' => ['nullable', 'date'],
            'latitude'      => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'     => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
