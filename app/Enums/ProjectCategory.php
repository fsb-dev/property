<?php

namespace App\Enums;

enum ProjectCategory: string
{
    // ── Residential ───────────────────────────────────────────
    case Apartment = 'apartment';
    case Flat      = 'flat';
    case Condo     = 'condo';
    case Duplex    = 'duplex';
    case Villa     = 'villa';
    case Townhouse = 'townhouse';

    // ── Commercial ────────────────────────────────────────────
    case OfficeSpace     = 'office_space';
    case ShopRetail      = 'shop_retail';
    case Showroom        = 'showroom';
    case CommercialFloor = 'commercial_floor';

    // ── Mixed Use ─────────────────────────────────────────────
    case ResidentialCommercial  = 'residential_commercial';
    case ShoppingMallApartment  = 'shopping_mall_apartment';
    case OfficeResidential      = 'office_residential';

    // ── Land / Plot ───────────────────────────────────────────
    case ResidentialPlot = 'residential_plot';
    case CommercialPlot  = 'commercial_plot';
    case IndustrialPlot  = 'industrial_plot';

    // ── Industrial ────────────────────────────────────────────
    case FactorySpace   = 'factory_space';
    case Warehouse      = 'warehouse';
    case IndustrialLand = 'industrial_land';

    public function label(): string
    {
        return match($this) {
            self::Apartment             => 'Apartment',
            self::Flat                  => 'Flat',
            self::Condo                 => 'Condo',
            self::Duplex                => 'Duplex',
            self::Villa                 => 'Villa',
            self::Townhouse             => 'Townhouse',
            self::OfficeSpace           => 'Office Space',
            self::ShopRetail            => 'Shop / Retail Space',
            self::Showroom              => 'Showroom',
            self::CommercialFloor       => 'Commercial Floor',
            self::ResidentialCommercial => 'Residential + Commercial',
            self::ShoppingMallApartment => 'Shopping Mall + Apartment',
            self::OfficeResidential     => 'Office + Residential',
            self::ResidentialPlot       => 'Residential Plot',
            self::CommercialPlot        => 'Commercial Plot',
            self::IndustrialPlot        => 'Industrial Plot',
            self::FactorySpace          => 'Factory Space',
            self::Warehouse             => 'Warehouse',
            self::IndustrialLand        => 'Industrial Land',
        };
    }

    public function type(): ProjectType
    {
        return match($this) {
            self::Apartment,
            self::Flat,
            self::Condo,
            self::Duplex,
            self::Villa,
            self::Townhouse             => ProjectType::Residential,

            self::OfficeSpace,
            self::ShopRetail,
            self::Showroom,
            self::CommercialFloor       => ProjectType::Commercial,

            self::ResidentialCommercial,
            self::ShoppingMallApartment,
            self::OfficeResidential     => ProjectType::MixedUse,

            self::ResidentialPlot,
            self::CommercialPlot,
            self::IndustrialPlot        => ProjectType::Land,

            self::FactorySpace,
            self::Warehouse,
            self::IndustrialLand        => ProjectType::Industrial,
        };
    }

    /** Determines whether the Unit module is available for this project category. */
    public function hasUnits(): bool
    {
        return match($this) {
            self::ResidentialPlot,
            self::CommercialPlot,
            self::IndustrialPlot,
            self::IndustrialLand,
            self::FactorySpace,
            self::Warehouse => false,

            default => true,
        };
    }

    /**
     * Category-specific spec fields for the dynamic form.
     * Each item: ['key', 'label', 'type' => text|number|select|boolean, 'suffix'?, 'options'?, 'span'?]
     */
    public function specFields(): array
    {
        return match($this) {

            // ── Residential ──────────────────────────────────
            self::Apartment => [
                ['key' => 'size_min_sqft', 'label' => 'Min Unit Size',     'type' => 'number', 'suffix' => 'sqft'],
                ['key' => 'size_max_sqft', 'label' => 'Max Unit Size',     'type' => 'number', 'suffix' => 'sqft'],
                ['key' => 'parking',       'label' => 'Parking Available', 'type' => 'boolean'],
            ],
            self::Flat => [
                ['key' => 'size_min_sqft', 'label' => 'Min Unit Size', 'type' => 'number', 'suffix' => 'sqft'],
                ['key' => 'size_max_sqft', 'label' => 'Max Unit Size', 'type' => 'number', 'suffix' => 'sqft'],
            ],
            self::Condo => [
                ['key' => 'size_min_sqft', 'label' => 'Min Unit Size',     'type' => 'number', 'suffix' => 'sqft'],
                ['key' => 'size_max_sqft', 'label' => 'Max Unit Size',     'type' => 'number', 'suffix' => 'sqft'],
                ['key' => 'parking',       'label' => 'Parking Available', 'type' => 'boolean'],
                ['key' => 'amenities',     'label' => 'Key Amenities',     'type' => 'text',   'span' => 2],
            ],
            self::Duplex => [
                ['key' => 'size_sqft',       'label' => 'Unit Size',       'type' => 'number', 'suffix' => 'sqft'],
                ['key' => 'floors_per_unit', 'label' => 'Floors per Unit', 'type' => 'number'],
            ],
            self::Villa => [
                ['key' => 'plot_size_katha', 'label' => 'Plot Size',         'type' => 'number', 'suffix' => 'katha'],
                ['key' => 'built_area_sqft', 'label' => 'Built Area',        'type' => 'number', 'suffix' => 'sqft'],
                ['key' => 'bedrooms',        'label' => 'Bedrooms',          'type' => 'number'],
                ['key' => 'bathrooms',       'label' => 'Bathrooms',         'type' => 'number'],
                ['key' => 'parking',         'label' => 'Parking Available', 'type' => 'boolean'],
            ],
            self::Townhouse => [
                ['key' => 'size_sqft',       'label' => 'Unit Size',       'type' => 'number', 'suffix' => 'sqft'],
                ['key' => 'floors_per_unit', 'label' => 'Floors per Unit', 'type' => 'number'],
                ['key' => 'bedrooms',        'label' => 'Bedrooms',        'type' => 'number'],
            ],

            // ── Commercial ───────────────────────────────────
            self::OfficeSpace => [
                ['key' => 'size_min_sqft',    'label' => 'Min Unit Size', 'type' => 'number', 'suffix' => 'sqft'],
                ['key' => 'size_max_sqft',    'label' => 'Max Unit Size', 'type' => 'number', 'suffix' => 'sqft'],
                ['key' => 'floor_plate_sqft', 'label' => 'Floor Plate',   'type' => 'number', 'suffix' => 'sqft'],
            ],
            self::ShopRetail => [
                ['key' => 'size_min_sqft', 'label' => 'Min Unit Size', 'type' => 'number', 'suffix' => 'sqft'],
                ['key' => 'size_max_sqft', 'label' => 'Max Unit Size', 'type' => 'number', 'suffix' => 'sqft'],
            ],
            self::Showroom => [
                ['key' => 'size_sqft',         'label' => 'Total Area',      'type' => 'number', 'suffix' => 'sqft'],
                ['key' => 'ceiling_height_ft', 'label' => 'Ceiling Height',  'type' => 'number', 'suffix' => 'ft'],
                ['key' => 'frontage_ft',       'label' => 'Frontage Width',  'type' => 'number', 'suffix' => 'ft'],
            ],
            self::CommercialFloor => [
                ['key' => 'floor_plate_sqft', 'label' => 'Floor Plate',   'type' => 'number', 'suffix' => 'sqft'],
                ['key' => 'size_min_sqft',    'label' => 'Min Unit Size', 'type' => 'number', 'suffix' => 'sqft'],
                ['key' => 'size_max_sqft',    'label' => 'Max Unit Size', 'type' => 'number', 'suffix' => 'sqft'],
            ],

            // ── Mixed Use ────────────────────────────────────
            self::ResidentialCommercial => [
                ['key' => 'residential_floors', 'label' => 'Residential Floors', 'type' => 'number'],
                ['key' => 'commercial_floors',  'label' => 'Commercial Floors',  'type' => 'number'],
            ],
            self::ShoppingMallApartment => [
                ['key' => 'mall_area_sqft',   'label' => 'Mall Area',        'type' => 'number', 'suffix' => 'sqft'],
                ['key' => 'apartment_floors', 'label' => 'Apartment Floors', 'type' => 'number'],
            ],
            self::OfficeResidential => [
                ['key' => 'office_floors',      'label' => 'Office Floors',      'type' => 'number'],
                ['key' => 'residential_floors', 'label' => 'Residential Floors', 'type' => 'number'],
            ],

            // ── Land / Plot ──────────────────────────────────
            self::ResidentialPlot,
            self::CommercialPlot => [
                ['key' => 'plot_size_katha', 'label' => 'Plot Size',   'type' => 'number',  'suffix' => 'katha'],
                ['key' => 'road_width_ft',   'label' => 'Road Width',  'type' => 'number',  'suffix' => 'ft'],
                ['key' => 'corner_plot',     'label' => 'Corner Plot', 'type' => 'boolean'],
                ['key' => 'facing',          'label' => 'Facing',      'type' => 'select',
                    'options' => ['North', 'South', 'East', 'West', 'North-East', 'North-West', 'South-East', 'South-West']],
            ],
            self::IndustrialPlot => [
                ['key' => 'plot_size_acres',     'label' => 'Plot Size',           'type' => 'number',  'suffix' => 'acres'],
                ['key' => 'road_width_ft',       'label' => 'Road Access Width',   'type' => 'number',  'suffix' => 'ft'],
                ['key' => 'utilities_available', 'label' => 'Utilities Available', 'type' => 'boolean'],
            ],

            // ── Industrial ───────────────────────────────────
            self::FactorySpace => [
                ['key' => 'floor_area_sqft',   'label' => 'Floor Area',     'type' => 'number', 'suffix' => 'sqft'],
                ['key' => 'ceiling_height_ft', 'label' => 'Ceiling Height', 'type' => 'number', 'suffix' => 'ft'],
                ['key' => 'power_supply_kw',   'label' => 'Power Supply',   'type' => 'number', 'suffix' => 'kW'],
                ['key' => 'loading_docks',     'label' => 'Loading Docks',  'type' => 'number'],
            ],
            self::Warehouse => [
                ['key' => 'floor_area_sqft',   'label' => 'Floor Area',     'type' => 'number', 'suffix' => 'sqft'],
                ['key' => 'ceiling_height_ft', 'label' => 'Ceiling Height', 'type' => 'number', 'suffix' => 'ft'],
                ['key' => 'loading_docks',     'label' => 'Loading Docks',  'type' => 'number'],
            ],
            self::IndustrialLand => [
                ['key' => 'plot_size_acres',     'label' => 'Plot Size',           'type' => 'number',  'suffix' => 'acres'],
                ['key' => 'road_access_ft',      'label' => 'Road Access Width',   'type' => 'number',  'suffix' => 'ft'],
                ['key' => 'utilities_available', 'label' => 'Utilities Available', 'type' => 'boolean'],
            ],
        };
    }

    /**
     * Returns grouped facility options for the project form checklist.
     * Each group: ['group' => string, 'items' => string[]]
     */
    public function facilities(): array
    {
        return match($this) {

            // ── Residential ──────────────────────────────────
            self::Apartment,
            self::Condo => [
                ['group' => 'Common Facilities',     'items' => ProjectFacilities::COMMON],
                ['group' => 'Residential Facilities','items' => ProjectFacilities::RESIDENTIAL],
                ['group' => 'Apartment Building',    'items' => ProjectFacilities::APARTMENT],
                ['group' => 'Luxury Facilities',     'items' => ProjectFacilities::LUXURY],
            ],
            self::Flat,
            self::Duplex => [
                ['group' => 'Common Facilities',     'items' => ProjectFacilities::COMMON],
                ['group' => 'Residential Facilities','items' => ProjectFacilities::RESIDENTIAL],
                ['group' => 'Apartment Building',    'items' => ProjectFacilities::APARTMENT],
            ],
            self::Villa => [
                ['group' => 'Common Facilities',     'items' => ProjectFacilities::COMMON],
                ['group' => 'Residential Facilities','items' => ProjectFacilities::RESIDENTIAL],
                ['group' => 'Luxury Facilities',     'items' => ProjectFacilities::LUXURY],
            ],
            self::Townhouse => [
                ['group' => 'Common Facilities',     'items' => ProjectFacilities::COMMON],
                ['group' => 'Residential Facilities','items' => ProjectFacilities::RESIDENTIAL],
            ],

            // ── Commercial ───────────────────────────────────
            self::OfficeSpace,
            self::ShopRetail,
            self::Showroom,
            self::CommercialFloor => [
                ['group' => 'Common Facilities',    'items' => ProjectFacilities::COMMON],
                ['group' => 'Commercial Facilities','items' => ProjectFacilities::COMMERCIAL],
            ],

            // ── Mixed Use ────────────────────────────────────
            self::ResidentialCommercial,
            self::ShoppingMallApartment,
            self::OfficeResidential => [
                ['group' => 'Common Facilities',     'items' => ProjectFacilities::COMMON],
                ['group' => 'Residential Facilities','items' => ProjectFacilities::RESIDENTIAL],
                ['group' => 'Commercial Facilities', 'items' => ProjectFacilities::COMMERCIAL],
                ['group' => 'Mixed Use Facilities',  'items' => ProjectFacilities::MIXED_USE],
            ],

            // ── Land / Plot ──────────────────────────────────
            self::ResidentialPlot,
            self::CommercialPlot,
            self::IndustrialPlot => [
                ['group' => 'Common Facilities',   'items' => ProjectFacilities::COMMON],
                ['group' => 'Land / Township',     'items' => ProjectFacilities::LAND],
            ],

            // ── Industrial ───────────────────────────────────
            self::FactorySpace,
            self::Warehouse,
            self::IndustrialLand => [
                ['group' => 'Common Facilities', 'items' => ProjectFacilities::COMMON],
            ],
        };
    }
}
