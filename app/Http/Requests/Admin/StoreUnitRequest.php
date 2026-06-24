<?php

namespace App\Http\Requests\Admin;

use App\Enums\UnitStatus;
use App\Enums\UnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'    => ['required', 'integer', 'exists:projects,id'],
            'unit_number'   => ['required', 'string', 'max:20'],
            'block'         => ['nullable', 'string', 'max:50'],
            'floor'         => ['nullable', 'integer', 'min:0', 'max:200'],
            'type'          => ['nullable', Rule::enum(UnitType::class)],
            'bedrooms'      => ['nullable', 'integer', 'min:0', 'max:10'],
            'size_sqft'     => ['nullable', 'integer', 'min:1'],
            'view'          => ['nullable', 'string', 'max:100'],
            'price'         => ['required', 'numeric', 'min:0'],
            'status'        => ['required', Rule::enum(UnitStatus::class)],
            'handover_date' => ['nullable', 'date'],
            'floor_plan'        => ['nullable', 'file', 'mimes:jpeg,png,webp,pdf', 'max:5120'],
            'new_images'        => ['nullable', 'array', 'max:20'],
            'new_images.*'      => ['image', 'mimes:jpeg,png,webp', 'max:5120'],
            'remove_images'     => ['nullable', 'array'],
            'remove_images.*'   => ['integer'],
            'new_documents'     => ['nullable', 'array', 'max:20'],
            'new_documents.*'   => ['file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:10240'],
            'remove_documents'  => ['nullable', 'array'],
            'remove_documents.*'=> ['integer'],
        ];
    }
}
