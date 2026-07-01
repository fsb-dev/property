<?php

namespace App\Http\Requests\Admin;

use App\Enums\UnitStatus;
use App\Enums\UnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Internal routing
            '_from'              => ['nullable', 'string'],

            // Identity
            'unit_number'        => ['required', 'string', 'max:30'],
            'unit_code'          => ['nullable', 'string', 'max:50'],
            'project_id'         => ['required', 'integer', 'exists:projects,id'],
            'block_id'           => ['nullable', 'integer', 'exists:project_blocks,id'],
            'floor'              => ['nullable', 'integer', 'min:0', 'max:300'],
            'floor_end'          => ['nullable', 'integer', 'min:0', 'max:300'],
            'type'               => ['nullable', Rule::enum(UnitType::class)],
            'wing'               => ['nullable', 'string', 'max:30'],
            'block'              => ['nullable', 'string', 'max:50'],
            'description'        => ['nullable', 'string', 'max:2000'],

            // Specifications
            'bedrooms'           => ['nullable', 'integer', 'min:0', 'max:20'],
            'bathrooms'          => ['nullable', 'integer', 'min:0', 'max:20'],
            'balconies'          => ['nullable', 'integer', 'min:0', 'max:10'],
            'servant_room'       => ['nullable', 'boolean'],
            'store_room'         => ['nullable', 'boolean'],
            'parking_spaces'     => ['nullable', 'integer', 'min:0', 'max:20'],
            'facing_direction'   => ['nullable', 'string', 'max:30'],
            'view'               => ['nullable', 'string', 'max:100'],

            // Measurements
            'size_sqft'          => ['nullable', 'integer', 'min:1'],
            'super_built_up_area' => ['nullable', 'integer', 'min:1'],
            'carpet_area'        => ['nullable', 'integer', 'min:1'],
            'ceiling_height'     => ['nullable', 'numeric', 'min:0', 'max:50'],
            'terrace_area'       => ['nullable', 'integer', 'min:0'],
            'parking_area'       => ['nullable', 'integer', 'min:0'],

            // Pricing
            'price'              => ['nullable', 'numeric', 'min:0'],
            'launch_price'       => ['nullable', 'numeric', 'min:0'],
            'current_price'      => ['nullable', 'numeric', 'min:0'],
            'parking_price'      => ['nullable', 'numeric', 'min:0'],
            'registration_fee'   => ['nullable', 'numeric', 'min:0'],
            'vat_pct'            => ['nullable', 'numeric', 'min:0', 'max:100'],
            'monthly_maintenance' => ['nullable', 'numeric', 'min:0'],
            'booking_amount'     => ['nullable', 'numeric', 'min:0'],

            // Media URLs
            'video_url'          => ['nullable', 'url', 'max:500'],
            'tour_360_url'       => ['nullable', 'url', 'max:500'],

            // Availability
            'status'             => ['required', Rule::enum(UnitStatus::class)],
            'launch_date'        => ['nullable', 'date'],
            'available_date'     => ['nullable', 'date'],
            'handover_date'      => ['nullable', 'date'],

            // Type-specific specs (residential / commercial / office / warehouse)
            'specs'              => ['nullable', 'array'],

            // Mortgage
            'eligible_banks'     => ['nullable', 'array'],
            'eligible_banks.*'   => ['string', 'max:100'],
            'max_loan_amount'    => ['nullable', 'numeric', 'min:0'],
            'payment_plan_months' => ['nullable', 'integer', 'min:1', 'max:600'],

            // Floor plan files
            'floor_plan'           => ['nullable', 'file', 'mimes:jpeg,png,webp', 'max:5120'],
            'remove_floor_plan'    => ['nullable', 'boolean'],
            'floor_plan_pdf'       => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'remove_floor_plan_pdf' => ['nullable', 'boolean'],
            'cad_dwg'              => ['nullable', 'file', 'max:20480'],
            'remove_cad_dwg'       => ['nullable', 'boolean'],

            // Media galleries
            'new_images'           => ['nullable', 'array', 'max:30'],
            'new_images.*'         => ['image', 'mimes:jpeg,png,webp', 'max:5120'],
            'remove_images'        => ['nullable', 'array'],
            'remove_images.*'      => ['integer'],
            'new_drone'            => ['nullable', 'array', 'max:20'],
            'new_drone.*'          => ['image', 'mimes:jpeg,png,webp', 'max:10240'],
            'remove_drone'         => ['nullable', 'array'],
            'remove_drone.*'       => ['integer'],
            'new_interior'         => ['nullable', 'array', 'max:30'],
            'new_interior.*'       => ['image', 'mimes:jpeg,png,webp', 'max:5120'],
            'remove_interior'      => ['nullable', 'array'],
            'remove_interior.*'    => ['integer'],
        ];
    }
}
