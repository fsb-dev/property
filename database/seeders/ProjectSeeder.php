<?php

namespace Database\Seeders;

use App\Enums\ProjectCategory;
use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use App\Enums\UnitStatus;
use App\Enums\UnitType;
use App\Models\Project;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    // Status cycle: ~60% available, 20% reserved, 20% sold
    private array $statusCycle = [
        UnitStatus::Available,
        UnitStatus::Available,
        UnitStatus::Available,
        UnitStatus::Reserved,
        UnitStatus::Sold,
    ];

    public function run(): void
    {
        $this->lakesideResidences();
        $this->greenValleyVillas();
        $this->pinnacleBusinessCenter();
        $this->skylineHeights();
        $this->horizonPlots();
        $this->palmBreezeTownhouses();
        $this->metroWarehouse();
    }

    // ── 1. Mixed Apartment + Duplex ───────────────────────────────────────────
    private function lakesideResidences(): void
    {
        $project = Project::create([
            'name'             => 'Lakeside Residences',
            'slug'             => 'lakeside-residences',
            'type'             => ProjectType::Residential,
            'category'         => ProjectCategory::Apartment,
            'status'           => ProjectStatus::UnderConstruction,
            'location'         => 'Bashundhara R/A, Dhaka',
            'address'          => 'Block D, Road 11, Bashundhara R/A, Dhaka-1229',
            'description'      => 'A premium residential tower offering lake-facing apartments and exclusive duplex units on the top floors. Built with modern finishes and world-class amenities.',
            'total_floors'     => 12,
            'total_units'      => 42,
            'overall_progress' => 65,
            'handover_date'    => '2027-06-30',
            'latitude'         => 23.8223,
            'longitude'        => 90.4265,
            'specifications'   => [
                'size_min_sqft' => 1200,
                'size_max_sqft' => 2800,
                'parking'       => true,
            ],
            'facilities' => [
                '24/7 Security', 'CCTV Surveillance', 'Generator', 'Parking',
                'Fire Safety System', 'Elevator / Lift', 'High-Speed Lift',
                'Basement Parking', 'Intercom System', 'Gas Supply',
                'Swimming Pool', 'Gym / Fitness Center', "Children's Play Area",
                'Rooftop Garden', 'Prayer Room / Mosque',
            ],
        ]);

        // Floors 1–10: 4 apartments per floor (2BR, 3BR, 2BR, 3BR)
        $unitIndex = 0;
        $blocks    = ['A', 'B', 'C', 'D'];
        $types     = [UnitType::TwoBed, UnitType::ThreeBed, UnitType::TwoBed, UnitType::ThreeBed];
        $sizes     = [1200, 1750, 1200, 1800];
        $prices    = [6_500_000, 9_800_000, 6_500_000, 10_200_000];

        for ($floor = 1; $floor <= 10; $floor++) {
            for ($u = 0; $u < 4; $u++) {
                Unit::create([
                    'project_id'  => $project->id,
                    'unit_number' => $blocks[$u] . '-' . str_pad($floor, 2, '0', STR_PAD_LEFT) . '0' . ($u + 1),
                    'block'       => 'Block ' . $blocks[$u],
                    'floor'       => $floor,
                    'type'        => $types[$u],
                    'bedrooms'    => $types[$u] === UnitType::TwoBed ? 2 : 3,
                    'size_sqft'   => $sizes[$u],
                    'view'        => $floor >= 7 ? 'Lake View' : 'City View',
                    'price'       => $prices[$u] + ($floor * 100_000),
                    'status'      => $this->statusCycle[$unitIndex % 5],
                ]);
                $unitIndex++;
            }
        }

        // Floors 11–12: 2 duplex units per floor (starting floor concept)
        $duplexData = [
            ['unit' => 'D-01', 'floor' => 11, 'view' => 'Panoramic Lake View'],
            ['unit' => 'D-02', 'floor' => 11, 'view' => 'Panoramic City View'],
            ['unit' => 'D-03', 'floor' => 12, 'view' => 'Panoramic Lake View'],
            ['unit' => 'D-04', 'floor' => 12, 'view' => 'Panoramic City View'],
        ];

        foreach ($duplexData as $d) {
            Unit::create([
                'project_id'  => $project->id,
                'unit_number' => $d['unit'],
                'block'       => null,
                'floor'       => $d['floor'],
                'type'        => UnitType::Duplex,
                'bedrooms'    => 4,
                'size_sqft'   => 2800,
                'view'        => $d['view'],
                'price'       => 19_500_000,
                'status'      => $this->statusCycle[$unitIndex % 5],
            ]);
            $unitIndex++;
        }
    }

    // ── 2. Villa Project ──────────────────────────────────────────────────────
    private function greenValleyVillas(): void
    {
        $project = Project::create([
            'name'          => 'Green Valley Villas',
            'slug'          => 'green-valley-villas',
            'type'          => ProjectType::Residential,
            'category'      => ProjectCategory::Villa,
            'status'        => ProjectStatus::Completed,
            'location'      => 'Purbachal New Town, Narayanganj',
            'address'       => 'Sector 7, Purbachal New Town, Narayanganj',
            'description'   => 'Exclusive gated villa community with private gardens, smart home integration and resort-style amenities. Each villa sits on a private plot with landscaped surroundings.',
            'total_floors'  => null,
            'total_units'   => 8,
            'handover_date' => '2025-12-01',
            'latitude'      => 23.7805,
            'longitude'     => 90.5123,
            'specifications' => [
                'plot_size_katha' => 5,
                'built_area_sqft' => 4200,
                'bedrooms'        => 5,
                'bathrooms'       => 5,
                'parking'         => true,
            ],
            'facilities' => [
                '24/7 Security', 'CCTV Surveillance', 'Generator', 'Water Supply',
                'Swimming Pool', 'Garden / Landscape Area', 'BBQ Area',
                'Prayer Room / Mosque', 'Guest Room', 'Driver Room',
                'Smart Home System', 'Private Pool', 'Concierge Service',
            ],
        ]);

        $villas = [
            ['unit' => 'V-01', 'phase' => 'Phase 1', 'bed' => 4, 'size' => 3800, 'price' => 22_000_000, 'status' => UnitStatus::Sold],
            ['unit' => 'V-02', 'phase' => 'Phase 1', 'bed' => 4, 'size' => 3800, 'price' => 22_000_000, 'status' => UnitStatus::Sold],
            ['unit' => 'V-03', 'phase' => 'Phase 1', 'bed' => 5, 'size' => 4500, 'price' => 32_000_000, 'status' => UnitStatus::Reserved],
            ['unit' => 'V-04', 'phase' => 'Phase 1', 'bed' => 5, 'size' => 4500, 'price' => 32_000_000, 'status' => UnitStatus::Available],
            ['unit' => 'V-05', 'phase' => 'Phase 2', 'bed' => 4, 'size' => 4000, 'price' => 25_000_000, 'status' => UnitStatus::Sold],
            ['unit' => 'V-06', 'phase' => 'Phase 2', 'bed' => 4, 'size' => 4000, 'price' => 25_000_000, 'status' => UnitStatus::Available],
            ['unit' => 'V-07', 'phase' => 'Phase 2', 'bed' => 5, 'size' => 4800, 'price' => 35_000_000, 'status' => UnitStatus::Available],
            ['unit' => 'V-08', 'phase' => 'Phase 2', 'bed' => 5, 'size' => 4800, 'price' => 35_000_000, 'status' => UnitStatus::Available],
        ];

        foreach ($villas as $v) {
            Unit::create([
                'project_id'  => $project->id,
                'unit_number' => $v['unit'],
                'block'       => $v['phase'],
                'floor'       => null,
                'type'        => UnitType::Villa,
                'bedrooms'    => $v['bed'],
                'size_sqft'   => $v['size'],
                'view'        => 'Garden View',
                'price'       => $v['price'],
                'status'      => $v['status'],
            ]);
        }
    }

    // ── 3. Commercial — Shops + Offices mixed floors ──────────────────────────
    private function pinnacleBusinessCenter(): void
    {
        $project = Project::create([
            'name'          => 'Pinnacle Business Center',
            'slug'          => 'pinnacle-business-center',
            'type'          => ProjectType::Commercial,
            'category'      => ProjectCategory::OfficeSpace,
            'status'        => ProjectStatus::Completed,
            'location'      => 'Motijheel, Dhaka',
            'address'       => 'Dilkusha C/A, Motijheel, Dhaka-1000',
            'description'   => 'A Grade-A commercial tower in Dhaka\'s financial district. Ground and first floors host retail shops; upper floors are modern open-plan offices with high-speed connectivity.',
            'total_floors'  => 8,
            'total_units'   => 40,
            'handover_date' => '2025-01-01',
            'latitude'      => 23.7279,
            'longitude'     => 90.4176,
            'specifications' => [
                'size_min_sqft'    => 400,
                'size_max_sqft'    => 3200,
                'floor_plate_sqft' => 12000,
            ],
            'facilities' => [
                '24/7 Security', 'CCTV Surveillance', 'Generator', 'Parking',
                'Reception / Lobby', 'Fire Safety System', 'Elevator / Lift',
                'Visitor Parking', 'Basement Parking',
                'Reception Area', 'Conference Room', 'Meeting Room',
                'Food Court', 'ATM Booth', 'Banking Facilities',
                'Central AC', 'High-Speed Internet',
            ],
        ]);

        $unitIndex = 0;

        // Levels 1–2: shops (8 per level)
        for ($level = 1; $level <= 2; $level++) {
            for ($s = 1; $s <= 8; $s++) {
                $size  = $s <= 4 ? 400 : 600;
                $price = $s <= 4 ? 3_500_000 : 6_000_000;
                Unit::create([
                    'project_id'  => $project->id,
                    'unit_number' => 'S-' . $level . 'F-' . str_pad($s, 2, '0', STR_PAD_LEFT),
                    'block'       => null,
                    'floor'       => $level,
                    'type'        => UnitType::Shop,
                    'bedrooms'    => null,
                    'size_sqft'   => $size,
                    'view'        => 'Street View',
                    'price'       => $price,
                    'status'      => $this->statusCycle[$unitIndex % 5],
                ]);
                $unitIndex++;
            }
        }

        // Levels 3–8: offices (4 per level)
        for ($level = 3; $level <= 8; $level++) {
            for ($o = 1; $o <= 4; $o++) {
                $size  = $o <= 2 ? 2400 : 3200;
                $price = $o <= 2 ? 8_000_000 : 12_000_000;
                Unit::create([
                    'project_id'  => $project->id,
                    'unit_number' => 'O-' . $level . str_pad($o, 2, '0', STR_PAD_LEFT),
                    'block'       => null,
                    'floor'       => $level,
                    'type'        => UnitType::Office,
                    'bedrooms'    => null,
                    'size_sqft'   => $size,
                    'view'        => $level >= 6 ? 'City Skyline' : 'City View',
                    'price'       => $price,
                    'status'      => $this->statusCycle[$unitIndex % 5],
                ]);
                $unitIndex++;
            }
        }
    }

    // ── 4. Mixed-Use — Shops + Apartments + Duplex + Penthouse ───────────────
    private function skylineHeights(): void
    {
        $project = Project::create([
            'name'             => 'Skyline Heights',
            'slug'             => 'skyline-heights',
            'type'             => ProjectType::MixedUse,
            'category'         => ProjectCategory::ResidentialCommercial,
            'status'           => ProjectStatus::UnderConstruction,
            'location'         => 'Gulshan 2, Dhaka',
            'address'          => 'Road 53, Gulshan Avenue, Gulshan 2, Dhaka-1212',
            'description'      => 'Dhaka\'s landmark mixed-use tower. Commercial retail on the first two levels, premium residential apartments from floor 3, exclusive duplex units on floor 15 and a sky penthouse crowning the 16th floor.',
            'total_floors'     => 16,
            'total_units'      => 50,
            'overall_progress' => 40,
            'handover_date'    => '2028-03-31',
            'latitude'         => 23.7947,
            'longitude'        => 90.4142,
            'specifications'   => [
                'residential_floors' => 14,
                'commercial_floors'  => 2,
            ],
            'facilities' => [
                '24/7 Security', 'CCTV Surveillance', 'Generator', 'Parking',
                'Fire Safety System', 'Elevator / Lift', 'High-Speed Lift',
                'Basement Parking', 'Intercom System',
                'Swimming Pool', 'Gym / Fitness Center', 'Rooftop Garden',
                'Reception Area', 'Conference Room', 'Food Court', 'ATM Booth',
                'Restaurants', 'Retail Shops', 'Parking Complex',
            ],
        ]);

        $unitIndex = 0;

        // Levels 1–2: commercial shops (6 per level)
        for ($level = 1; $level <= 2; $level++) {
            for ($s = 1; $s <= 6; $s++) {
                Unit::create([
                    'project_id'  => $project->id,
                    'unit_number' => 'C-' . $level . 'F-' . str_pad($s, 2, '0', STR_PAD_LEFT),
                    'block'       => null,
                    'floor'       => $level,
                    'type'        => UnitType::Shop,
                    'bedrooms'    => null,
                    'size_sqft'   => 500,
                    'view'        => 'Street Front',
                    'price'       => 5_500_000,
                    'status'      => $this->statusCycle[$unitIndex % 5],
                ]);
                $unitIndex++;
            }
        }

        // Floors 3–14: residential apartments (3 per floor — 2BR, 3BR, 2BR)
        $resTypes  = [UnitType::TwoBed, UnitType::ThreeBed, UnitType::TwoBed];
        $resSizes  = [1350, 1900, 1350];
        $resPrices = [7_500_000, 11_500_000, 7_500_000];

        for ($floor = 3; $floor <= 14; $floor++) {
            for ($u = 0; $u < 3; $u++) {
                Unit::create([
                    'project_id'  => $project->id,
                    'unit_number' => 'R-' . str_pad($floor, 2, '0', STR_PAD_LEFT) . ($u + 1),
                    'block'       => null,
                    'floor'       => $floor,
                    'type'        => $resTypes[$u],
                    'bedrooms'    => $resTypes[$u] === UnitType::TwoBed ? 2 : 3,
                    'size_sqft'   => $resSizes[$u],
                    'view'        => $floor >= 10 ? 'Panoramic City View' : 'City View',
                    'price'       => $resPrices[$u] + ($floor * 150_000),
                    'status'      => $this->statusCycle[$unitIndex % 5],
                ]);
                $unitIndex++;
            }
        }

        // Floor 15: 2 duplex units (spans 15–16 internally)
        foreach (['DX-01', 'DX-02'] as $i => $unitNo) {
            Unit::create([
                'project_id'  => $project->id,
                'unit_number' => $unitNo,
                'block'       => null,
                'floor'       => 15,
                'type'        => UnitType::Duplex,
                'bedrooms'    => 4,
                'size_sqft'   => 3200,
                'view'        => 'Panoramic 360° View',
                'price'       => 28_000_000,
                'status'      => $i === 0 ? UnitStatus::Reserved : UnitStatus::Available,
            ]);
            $unitIndex++;
        }

        // Floor 16: sky penthouse
        Unit::create([
            'project_id'  => $project->id,
            'unit_number' => 'PH-01',
            'block'       => null,
            'floor'       => 16,
            'type'        => UnitType::Penthouse,
            'bedrooms'    => 5,
            'size_sqft'   => 5000,
            'view'        => 'Sky & City Panorama',
            'price'       => 55_000_000,
            'status'      => UnitStatus::Available,
        ]);
    }

    // ── 5. Land / Plot — no units ─────────────────────────────────────────────
    private function horizonPlots(): void
    {
        Project::create([
            'name'        => 'Horizon Plots — Purbachal',
            'slug'        => 'horizon-plots-purbachal',
            'type'        => ProjectType::Land,
            'category'    => ProjectCategory::ResidentialPlot,
            'status'      => ProjectStatus::Planning,
            'location'    => 'Purbachal New Town, Narayanganj',
            'address'     => 'Sector 12, Purbachal New Town',
            'description' => 'Ready residential plots in a fully planned township with internal roads, drainage, utility connections and a boundary wall. Corner plots available.',
            'total_floors' => null,
            'total_units'  => null,
            'handover_date'=> '2026-12-31',
            'latitude'    => 23.7932,
            'longitude'   => 90.5301,
            'specifications' => [
                'plot_size_katha' => 5,
                'road_width_ft'   => 20,
                'corner_plot'     => false,
                'facing'          => 'North',
            ],
            'facilities' => [
                '24/7 Security', 'Security Gate', 'Boundary Wall',
                'Internal Roads', 'Drainage System',
                'Electricity Connection', 'Water Connection',
                'Community Center', 'Lake / Park',
            ],
        ]);
    }

    // ── 6. Townhouse Project ──────────────────────────────────────────────────
    private function palmBreezeTownhouses(): void
    {
        $project = Project::create([
            'name'          => 'Palm Breeze Townhouses',
            'slug'          => 'palm-breeze-townhouses',
            'type'          => ProjectType::Residential,
            'category'      => ProjectCategory::Townhouse,
            'status'        => ProjectStatus::Completed,
            'location'      => 'Uttara Sector 11, Dhaka',
            'address'       => 'Road 7, Sector 11, Uttara, Dhaka-1230',
            'description'   => 'Boutique gated townhouse community with 12 three-story homes across two phases. Each unit has a private rooftop terrace and dedicated parking.',
            'total_floors'  => 3,
            'total_units'   => 12,
            'handover_date' => '2025-06-01',
            'latitude'      => 23.8759,
            'longitude'     => 90.3985,
            'specifications' => [
                'size_sqft'       => 2400,
                'floors_per_unit' => 3,
                'bedrooms'        => 3,
            ],
            'facilities' => [
                '24/7 Security', 'CCTV Surveillance', 'Generator', 'Parking',
                'Garden / Landscape Area', 'Walking Track',
                'Community Hall', 'Prayer Room / Mosque',
            ],
        ]);

        $townhouses = [
            // Phase 1
            ['unit' => 'TH-01', 'phase' => 'Phase 1', 'size' => 2200, 'price' => 13_500_000, 'status' => UnitStatus::Sold],
            ['unit' => 'TH-02', 'phase' => 'Phase 1', 'size' => 2200, 'price' => 13_500_000, 'status' => UnitStatus::Sold],
            ['unit' => 'TH-03', 'phase' => 'Phase 1', 'size' => 2400, 'price' => 14_500_000, 'status' => UnitStatus::Sold],
            ['unit' => 'TH-04', 'phase' => 'Phase 1', 'size' => 2400, 'price' => 14_500_000, 'status' => UnitStatus::Reserved],
            ['unit' => 'TH-05', 'phase' => 'Phase 1', 'size' => 2200, 'price' => 13_500_000, 'status' => UnitStatus::Available],
            ['unit' => 'TH-06', 'phase' => 'Phase 1', 'size' => 2200, 'price' => 13_500_000, 'status' => UnitStatus::Available],
            // Phase 2
            ['unit' => 'TH-07', 'phase' => 'Phase 2', 'size' => 2400, 'price' => 15_500_000, 'status' => UnitStatus::Available],
            ['unit' => 'TH-08', 'phase' => 'Phase 2', 'size' => 2400, 'price' => 15_500_000, 'status' => UnitStatus::Available],
            ['unit' => 'TH-09', 'phase' => 'Phase 2', 'size' => 2600, 'price' => 17_000_000, 'status' => UnitStatus::Available],
            ['unit' => 'TH-10', 'phase' => 'Phase 2', 'size' => 2600, 'price' => 17_000_000, 'status' => UnitStatus::Reserved],
            ['unit' => 'TH-11', 'phase' => 'Phase 2', 'size' => 2400, 'price' => 15_500_000, 'status' => UnitStatus::Available],
            ['unit' => 'TH-12', 'phase' => 'Phase 2', 'size' => 2400, 'price' => 15_500_000, 'status' => UnitStatus::Available],
        ];

        foreach ($townhouses as $t) {
            Unit::create([
                'project_id'  => $project->id,
                'unit_number' => $t['unit'],
                'block'       => $t['phase'],
                'floor'       => null,
                'type'        => UnitType::Townhouse,
                'bedrooms'    => 3,
                'size_sqft'   => $t['size'],
                'view'        => 'Garden View',
                'price'       => $t['price'],
                'status'      => $t['status'],
            ]);
        }
    }

    // ── 7. Industrial Warehouse — no units ────────────────────────────────────
    private function metroWarehouse(): void
    {
        Project::create([
            'name'        => 'Metro Industrial Warehouse',
            'slug'        => 'metro-industrial-warehouse',
            'type'        => ProjectType::Industrial,
            'category'    => ProjectCategory::Warehouse,
            'status'      => ProjectStatus::Completed,
            'location'    => 'Gazipur Sadar, Gazipur',
            'address'     => 'Bhawal National Park Road, Gazipur Sadar, Gazipur-1700',
            'description' => 'Large-format modern warehouse facility with wide-span structure, high ceilings, and full logistical infrastructure. Ideal for cold storage, distribution or manufacturing.',
            'total_floors' => null,
            'total_units'  => null,
            'handover_date'=> '2025-03-01',
            'latitude'    => 23.9999,
            'longitude'   => 90.4074,
            'specifications' => [
                'floor_area_sqft'   => 45000,
                'ceiling_height_ft' => 30,
                'loading_docks'     => 8,
            ],
            'facilities' => [
                '24/7 Security', 'CCTV Surveillance', 'Generator',
                'Fire Safety System', 'Fire Exit', 'Emergency Alarm',
                'Waste Management',
            ],
        ]);
    }
}
