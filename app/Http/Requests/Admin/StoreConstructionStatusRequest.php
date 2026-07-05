<?php

namespace App\Http\Requests\Admin;

use App\Enums\ConstructionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConstructionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'    => ['required', 'exists:projects,id'],
            'status'        => ['required', Rule::enum(ConstructionStatus::class)],
            'time_progress' => ['required', 'numeric', 'min:0', 'max:100'],
            'quality_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'budget_total'  => ['required', 'numeric', 'min:0'],
            'budget_used'   => ['required', 'numeric', 'min:0', 'lte:budget_total'],
        ];
    }
}
