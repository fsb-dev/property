<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $projectId = $this->route('project')?->id;

        return [
            // Step 1 — Identity
            'name'             => ['required', 'string', 'max:255', 'unique:projects,name'],
            'project_code'  => ['nullable', 'string', 'max:20', Rule::unique('projects', 'project_code')->ignore($projectId)],
            'type'          => ['required', Rule::enum(ProjectType::class)],
            'status'        => ['required', Rule::enum(ProjectStatus::class)],
            'theme_color'   => ['nullable', 'string', 'max:7'],
            'start_date'    => ['nullable', 'date'],
            'handover_date' => ['nullable', 'date'],
            'description'   => ['nullable', 'string'],

            // Step 2 — Location
            'location'  => ['nullable', 'string', 'max:255'],
            'address'   => ['nullable', 'string', 'max:500'],
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            // Step 3 — Buildings & Sections
            'buildings'                              => ['nullable', 'array'],
            'buildings.*.id'                         => ['nullable', 'integer', 'exists:project_buildings,id'],
            'buildings.*.name'                       => ['required', 'string', 'max:100'],
            'buildings.*.total_floors'               => ['nullable', 'integer', 'min:1', 'max:300'],
            'buildings.*.specifications'             => ['nullable', 'array'],
            'buildings.*.sections'                   => ['nullable', 'array'],
            'buildings.*.sections.*.id'              => ['nullable', 'integer', 'exists:project_blocks,id'],
            'buildings.*.sections.*.name'            => ['nullable', 'string', 'max:100'],
            'buildings.*.sections.*.type'            => ['required', 'string', 'max:50'],
            'buildings.*.sections.*.floor_start'     => ['nullable', 'integer', 'min:0', 'max:300'],
            'buildings.*.sections.*.floor_end'       => ['nullable', 'integer', 'min:0', 'max:300'],
            'buildings.*.sections.*.planned_units'   => ['nullable', 'integer', 'min:0', 'max:5000'],
            'buildings.*.sections.*.specifications'  => ['nullable', 'array'],

            // Step 4 — Financials
            'developer_id'        => ['nullable', 'integer', 'exists:developers,id'],
            'developer_name'      => ['nullable', 'string', 'max:255'],
            'land_area'           => ['nullable', 'numeric', 'min:0'],
            'land_area_unit'      => ['nullable', 'string', 'max:20'],
            'built_up_area'       => ['nullable', 'numeric', 'min:0'],
            'estimated_value'     => ['nullable', 'integer', 'min:0'],
            'booking_amount'      => ['nullable', 'numeric', 'min:0'],
            'booking_amount_type' => ['nullable', 'string', Rule::in(['fixed', 'percentage'])],
            'commission_pct'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_plan_months' => ['nullable', 'integer', 'min:1', 'max:600'],
            'service_charge_sqft' => ['nullable', 'numeric', 'min:0'],
            'maintenance_years'   => ['nullable', 'integer', 'min:0', 'max:50'],

            // Step 5 — Facilities
            'facility_ids'   => ['nullable', 'array'],
            'facility_ids.*' => ['integer', 'exists:facilities,id'],

            // Step 6 — Compliance
            'compliances'                 => ['nullable', 'array'],
            'compliances.*.id'            => ['nullable', 'integer', 'exists:project_compliances,id'],
            'compliances.*.name'          => ['required', 'string', 'max:200'],
            'compliances.*.type'          => ['required', 'string', Rule::in(['approval', 'certification'])],
            'compliances.*.status'        => ['required', 'string', Rule::in(['pending', 'obtained', 'not_required'])],
            'compliances.*.obtained_date' => ['nullable', 'date'],

            // Step 7 — Media & Docs
            'cover'              => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'remove_cover'       => ['nullable', 'boolean'],
            'new_images'         => ['nullable', 'array', 'max:20'],
            'new_images.*'       => ['image', 'mimes:jpeg,png,webp', 'max:5120'],
            'remove_images'      => ['nullable', 'array'],
            'remove_images.*'    => ['integer'],
            'new_documents'      => ['nullable', 'array', 'max:20'],
            'new_documents.*'    => ['file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:10240'],
            'remove_documents'   => ['nullable', 'array'],
            'remove_documents.*' => ['integer'],
        ];
    }
}
