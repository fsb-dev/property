<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProjectCategory;
use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
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
            'handover_date' => ['nullable', 'date', 'after:today'],
            'latitude'      => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'     => ['nullable', 'numeric', 'between:-180,180'],
            'cover'         => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'new_images'    => ['nullable', 'array', 'max:20'],
            'new_images.*'  => ['image', 'mimes:jpeg,png,webp', 'max:5120'],
            'remove_images'     => ['nullable', 'array'],
            'remove_images.*'   => ['integer'],
            'new_documents'     => ['nullable', 'array', 'max:20'],
            'new_documents.*'   => ['file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:10240'],
            'remove_documents'  => ['nullable', 'array'],
            'remove_documents.*'=> ['integer'],
        ];
    }
}
