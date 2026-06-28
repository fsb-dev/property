<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use App\Enums\UnitStatus;
use App\Enums\UnitType;
use App\Models\Developer;
use App\Models\Facility;
use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    private array $statusCycle = [
        UnitStatus::Available,
        UnitStatus::Available,
        UnitStatus::Available,
        UnitStatus::Reserved,
        UnitStatus::Sold,
    ];

    private int $statusIdx = 0;

    private function nextStatus(): UnitStatus
    {
        $s = $this->statusCycle[$this->statusIdx % count($this->statusCycle)];
        $this->statusIdx++;
        return $s;
    }

    public function run(): void
    {
        // ── Developers ────────────────────────────────────────────
        $dev1 = Developer::create(['name' => 'Skyline Builders Ltd.',   'phone' => '+8801711000001', 'email' => 'info@skylinebd.com']);
        $dev2 = Developer::create(['name' => 'Green Urban Properties',  'phone' => '+8801711000002', 'email' => 'info@greenurban.com']);
        $dev3 = Developer::create(['name' => 'Pinnacle Developments',   'phone' => '+8801711000003', 'email' => 'info@pinnacle.com']);

        // ── Facility shortcuts ────────────────────────────────────
        $f = fn(array $names) => Facility::whereIn('name', $names)->pluck('id')->toArray();

        $this->lakesideResidences($dev1, $f);
        $this->greenValleyVillas($dev2, $f);
        $this->pinnacleBusinessCenter($dev3, $f);
        $this->skylineHeights($dev1, $f);
        $this->horizonPlots($dev2, $f);
        $this->palmBreezeTownhouses($dev2, $f);
        $this->metroWarehouse($dev3, $f);
    }

    // ── 1. Mixed Apartment + Duplex ───────────────────────────────
    private function lakesideResidences(Developer $dev, callable $f): void
    {
        $project = Project::create([
            'name'                => 'Lakeside Residences',
            'slug'                => 'lakeside-residences',
            'project_code'        => 'LKS-2024',
            'theme_color'         => '#5B3DF5',
            'developer_id'        => $dev->id,
            'type'                => ProjectType::Residential,
            'status'              => ProjectStatus::UnderConstruction,
            'location'            => 'Bashundhara R/A, Dhaka',
            'address'             => 'Block D, Road 11, Bashundhara R/A, Dhaka-1229',
            'description'         => 'A premium residential tower offering lake-facing apartments and exclusive duplex units on the top floors. Built with modern finishes and world-class amenities.',
            'start_date'          => '2023-01-15',
            'handover_date'       => '2027-06-30',
            'total_floors'        => 12,
            'total_units'         => 42,
            'overall_progress'    => 65,
            'land_area'           => 18,
            'land_area_unit'      => 'katha',
            'built_up_area'       => 52000,
            'estimated_value'     => 480000000,
            'booking_amount'      => 500000,
            'booking_amount_type' => 'fixed',
            'commission_pct'      => 2.5,
            'payment_plan_months' => 36,
            'latitude'            => 23.8223,
            'longitude'           => 90.4265,
        ]);

        // Building-level: infrastructure shared by the whole tower
        $building = ProjectBuilding::create([
            'project_id'    => $project->id,
            'name'          => 'Tower A',
            'total_floors'  => 12,
            'specifications' => [
                'passenger_lifts'  => 3,
                'service_lifts'    => 1,
                'parking_levels'   => 2,
                'parking_capacity' => 60,
                'lobby_type'       => 'Grand double-height',
                'security'         => 'Biometric Access',
                'generator'        => 'Full Backup',
            ],
            'sort_order' => 0,
        ]);

        // Section: all floors are residential apartments
        $block = $building->sections()->create([
            'project_id'    => $project->id,
            'name'          => 'Apartments',
            'type'          => 'residential',
            'floor_start'   => 1,
            'floor_end'     => 12,
            'planned_units' => 42,
            'specifications' => ['hvac' => 'Split Units'],
            'sort_order'    => 0,
        ]);

        $project->facilities()->sync($f([
            'Swimming Pool', 'Gym / Fitness Center', 'Rooftop Garden', "Children's Play Area",
            '24/7 Security', 'CCTV Surveillance', 'Elevator / Lift', 'Basement Parking',
            'Prayer Room / Mosque', 'Community Hall',
        ]));

        $this->statusIdx = 0;
        for ($floor = 1; $floor <= 10; $floor++) {
            for ($pos = 1; $pos <= 4; $pos++) {
                Unit::create([
                    'project_id'  => $project->id,
                    'block_id'    => $block->id,
                    'block'       => 'Tower A',
                    'unit_number' => "A-{$floor}0{$pos}",
                    'floor'       => $floor,
                    'sort_order'  => $pos - 1,
                    'type'        => UnitType::TwoBed,
                    'bedrooms'    => 2,
                    'size_sqft'   => 1150,
                    'view'        => $pos <= 2 ? 'Lake View' : 'City View',
                    'price'       => $pos <= 2 ? 9500000 + ($floor * 50000) : 8500000 + ($floor * 50000),
                    'status'      => $this->nextStatus(),
                ]);
            }
        }
        for ($floor = 11; $floor <= 12; $floor++) {
            Unit::create([
                'project_id'  => $project->id,
                'block_id'    => $block->id,
                'block'       => 'Tower A',
                'unit_number' => "A-{$floor}01",
                'floor'       => $floor,
                'sort_order'  => 0,
                'type'        => UnitType::Duplex,
                'bedrooms'    => 4,
                'size_sqft'   => 2800,
                'view'        => 'Panoramic Lake View',
                'price'       => 28000000,
                'status'      => $this->nextStatus(),
            ]);
        }
    }

    // ── 2. Villa Community ────────────────────────────────────────
    private function greenValleyVillas(Developer $dev, callable $f): void
    {
        $project = Project::create([
            'name'                => 'Green Valley Villas',
            'slug'                => 'green-valley-villas',
            'project_code'        => 'GVV-2023',
            'theme_color'         => '#1F9D6B',
            'developer_id'        => $dev->id,
            'type'                => ProjectType::Residential,
            'status'              => ProjectStatus::Completed,
            'location'            => 'Purbachal New Town, Dhaka',
            'address'             => 'Sector 9, Purbachal R/A, Dhaka-1461',
            'description'         => 'A gated villa community offering spacious single and twin villas with private gardens, solar panels, and premium security.',
            'start_date'          => '2021-06-01',
            'handover_date'       => '2024-12-31',
            'total_units'         => 24,
            'overall_progress'    => 100,
            'land_area'           => 90,
            'land_area_unit'      => 'katha',
            'estimated_value'     => 720000000,
            'booking_amount'      => 10,
            'booking_amount_type' => 'percentage',
            'commission_pct'      => 3.0,
            'latitude'            => 23.8703,
            'longitude'           => 90.5289,
        ]);

        $building = ProjectBuilding::create([
            'project_id'    => $project->id,
            'name'          => 'Cluster A',
            'specifications' => [
                'security'  => '24/7 Manned',
                'generator' => 'Full Backup',
            ],
            'sort_order' => 0,
        ]);

        $block = $building->sections()->create([
            'project_id'    => $project->id,
            'name'          => 'Villas',
            'type'          => 'villa',
            'planned_units' => 24,
            'specifications' => ['cargo_access' => 'Dedicated Ramp'],
            'sort_order'    => 0,
        ]);

        $project->facilities()->sync($f([
            'Swimming Pool', 'Club House', 'Garden / Landscape Area', 'Walking Track',
            '24/7 Security', 'Security Gate', 'Solar Power', 'Community Center',
        ]));

        $this->statusIdx = 0;
        $types  = [UnitType::Villa, UnitType::Villa, UnitType::Villa, UnitType::Townhouse];
        $sizes  = [2800, 2800, 3500, 2200];
        $prices = [22000000, 22000000, 32000000, 17000000];

        for ($i = 1; $i <= 24; $i++) {
            $t = $types[($i - 1) % 4];
            Unit::create([
                'project_id'  => $project->id,
                'block_id'    => $block->id,
                'block'       => 'Cluster A',
                'unit_number' => "V-{$i}",
                'sort_order'  => $i - 1,
                'type'        => $t,
                'bedrooms'    => $t === UnitType::Townhouse ? 3 : 4,
                'size_sqft'   => $sizes[($i - 1) % 4],
                'price'       => $prices[($i - 1) % 4],
                'status'      => $this->nextStatus(),
            ]);
        }
    }

    // ── 3. Mixed-Use Commercial Tower ─────────────────────────────
    // Demonstrates the core use case: floors 1-4 retail, floors 5-18 office
    private function pinnacleBusinessCenter(Developer $dev, callable $f): void
    {
        $project = Project::create([
            'name'             => 'Pinnacle Business Center',
            'slug'             => 'pinnacle-business-center',
            'project_code'     => 'PBC-2025',
            'theme_color'      => '#2A7DE1',
            'developer_id'     => $dev->id,
            'type'             => ProjectType::Commercial,
            'status'           => ProjectStatus::Planning,
            'location'         => 'Gulshan 2, Dhaka',
            'address'          => 'Plot 14, Road 103, Gulshan-2, Dhaka-1212',
            'description'      => 'A Grade-A commercial tower with flexible office suites and retail spaces, targeting corporate tenants and SMEs.',
            'start_date'       => '2025-04-01',
            'handover_date'    => '2028-09-30',
            'total_floors'     => 18,
            'total_units'      => 36,
            'overall_progress' => 8,
            'land_area'        => 24,
            'land_area_unit'   => 'katha',
            'built_up_area'    => 85000,
            'estimated_value'  => 1200000000,
            'commission_pct'   => 2.0,
            'latitude'         => 23.7958,
            'longitude'        => 90.4146,
        ]);

        // One building — shared infrastructure for all floors
        $building = ProjectBuilding::create([
            'project_id'    => $project->id,
            'name'          => 'Pinnacle Tower',
            'total_floors'  => 18,
            'specifications' => [
                'passenger_lifts'  => 4,
                'service_lifts'    => 2,
                'parking_levels'   => 3,
                'parking_capacity' => 120,
                'lobby_type'       => 'Grand double-height',
                'security'         => 'Biometric Access',
                'generator'        => 'Full Backup',
            ],
            'sort_order' => 0,
        ]);

        // Section 1: Retail Podium (floors 1-4) — commercial zone
        $retailBlock = $building->sections()->create([
            'project_id'    => $project->id,
            'name'          => 'Retail Podium',
            'type'          => 'commercial',
            'floor_start'   => 1,
            'floor_end'     => 4,
            'planned_units' => 8,
            'specifications' => [
                'hvac'         => 'Central Chiller',
                'cargo_access' => 'Dedicated Ramp',
            ],
            'sort_order' => 0,
        ]);

        // Section 2: Office Floors (5-18) — office zone with premium spec
        $officeBlock = $building->sections()->create([
            'project_id'    => $project->id,
            'name'          => 'Office Floors',
            'type'          => 'office',
            'floor_start'   => 5,
            'floor_end'     => 18,
            'planned_units' => 28,
            'specifications' => [
                'hvac'                => 'Central Chiller',
                'cargo_access'        => 'Dedicated Ramp',
                'internet'            => 'Dual-Redundant Fiber',
                'electrical_capacity' => '2.5 MVA',
            ],
            'sort_order' => 1,
        ]);

        $project->facilities()->sync($f([
            'Reception Area', 'Conference Room', 'Food Court', 'Central AC',
            'High-Speed Internet', '24/7 Security', 'CCTV Surveillance',
            'Elevator / Lift', 'Basement Parking', 'ATM Booth',
        ]));

        $this->statusIdx = 0;
        for ($i = 1; $i <= 8; $i++) {
            Unit::create([
                'project_id'  => $project->id,
                'block_id'    => $retailBlock->id,
                'block'       => 'Retail Podium',
                'unit_number' => "R-{$i}",
                'floor'       => (int) ceil($i / 2),
                'sort_order'  => ($i - 1) % 2,
                'type'        => UnitType::Shop,
                'size_sqft'   => 650,
                'price'       => 8500000,
                'status'      => $this->nextStatus(),
            ]);
        }
        for ($floor = 5; $floor <= 18; $floor++) {
            for ($pos = 1; $pos <= 2; $pos++) {
                Unit::create([
                    'project_id'  => $project->id,
                    'block_id'    => $officeBlock->id,
                    'block'       => 'Office Tower',
                    'unit_number' => "O-{$floor}0{$pos}",
                    'floor'       => $floor,
                    'sort_order'  => $pos - 1,
                    'type'        => UnitType::Office,
                    'size_sqft'   => $pos === 1 ? 7000 : 4500,
                    'price'       => $pos === 1 ? 95000 * 7000 : 95000 * 4500,
                    'status'      => $this->nextStatus(),
                ]);
            }
        }
    }

    // ── 4. Skyline Heights ────────────────────────────────────────
    private function skylineHeights(Developer $dev, callable $f): void
    {
        $project = Project::create([
            'name'                => 'Skyline Heights',
            'slug'                => 'skyline-heights',
            'project_code'        => 'SKH-2026',
            'theme_color'         => '#E0902B',
            'developer_id'        => $dev->id,
            'type'                => ProjectType::Residential,
            'status'              => ProjectStatus::Planning,
            'location'            => 'Uttara Sector 7, Dhaka',
            'address'             => 'House 12, Road 7, Sector 7, Uttara, Dhaka-1230',
            'description'         => 'A contemporary high-rise offering studio to 3-bedroom apartments with panoramic city views.',
            'start_date'          => '2026-03-01',
            'handover_date'       => '2029-12-31',
            'total_floors'        => 20,
            'total_units'         => 80,
            'overall_progress'    => 3,
            'land_area'           => 22,
            'land_area_unit'      => 'katha',
            'estimated_value'     => 650000000,
            'booking_amount'      => 300000,
            'booking_amount_type' => 'fixed',
            'commission_pct'      => 2.5,
            'payment_plan_months' => 48,
            'latitude'            => 23.8759,
            'longitude'           => 90.3795,
        ]);

        $building = ProjectBuilding::create([
            'project_id'    => $project->id,
            'name'          => 'Tower A',
            'total_floors'  => 20,
            'specifications' => [
                'passenger_lifts'  => 3,
                'service_lifts'    => 1,
                'parking_levels'   => 2,
                'parking_capacity' => 80,
                'security'         => 'Access Card',
                'generator'        => 'Partial Backup',
            ],
            'sort_order' => 0,
        ]);

        $block = $building->sections()->create([
            'project_id'    => $project->id,
            'name'          => 'Apartments',
            'type'          => 'residential',
            'floor_start'   => 1,
            'floor_end'     => 20,
            'planned_units' => 80,
            'specifications' => [],
            'sort_order'    => 0,
        ]);

        $project->facilities()->sync($f([
            'Gym / Fitness Center', 'Rooftop Garden', "Children's Play Area",
            '24/7 Security', 'Elevator / Lift', 'Visitor Parking',
        ]));

        $this->statusIdx = 0;
        $unitTypes = [UnitType::Studio, UnitType::TwoBed, UnitType::TwoBed, UnitType::ThreeBed];
        $sizes     = [520, 1050, 1150, 1650];
        $prices    = [4500000, 7800000, 8500000, 12000000];

        for ($floor = 1; $floor <= 20; $floor++) {
            for ($pos = 0; $pos < 4; $pos++) {
                Unit::create([
                    'project_id'  => $project->id,
                    'block_id'    => $block->id,
                    'block'       => 'Tower A',
                    'unit_number' => 'A-' . $floor . str_pad($pos + 1, 2, '0', STR_PAD_LEFT),
                    'floor'       => $floor,
                    'sort_order'  => $pos,
                    'type'        => $unitTypes[$pos],
                    'bedrooms'    => $pos === 0 ? 0 : ($pos === 3 ? 3 : 2),
                    'size_sqft'   => $sizes[$pos],
                    'price'       => $prices[$pos] + ($floor * 20000),
                    'status'      => $this->nextStatus(),
                ]);
            }
        }
    }

    // ── 5. Horizon Plots ──────────────────────────────────────────
    private function horizonPlots(Developer $dev, callable $f): void
    {
        $project = Project::create([
            'name'                => 'Horizon Plots',
            'slug'                => 'horizon-plots',
            'project_code'        => 'HRP-2024',
            'theme_color'         => '#E5484D',
            'developer_id'        => $dev->id,
            'type'                => ProjectType::Land,
            'status'              => ProjectStatus::Completed,
            'location'            => 'Keraniganj, Dhaka',
            'address'             => 'Mouza Bhoirab, Keraniganj, Dhaka',
            'description'         => 'Registered residential plots in a gated community with all utilities in place.',
            'total_units'         => 48,
            'overall_progress'    => 100,
            'land_area'           => 180,
            'land_area_unit'      => 'katha',
            'estimated_value'     => 320000000,
            'booking_amount'      => 500000,
            'booking_amount_type' => 'fixed',
            'latitude'            => 23.7236,
            'longitude'           => 90.3512,
        ]);

        $building = ProjectBuilding::create([
            'project_id' => $project->id,
            'name'       => 'Phase 1',
            'sort_order' => 0,
        ]);

        $block = $building->sections()->create([
            'project_id'    => $project->id,
            'name'          => 'Plots',
            'type'          => 'residential',
            'planned_units' => 48,
            'sort_order'    => 0,
        ]);

        $project->facilities()->sync($f([
            'Internal Roads', 'Drainage System', 'Electricity Connection',
            'Water Connection', 'Boundary Wall', 'Security Gate',
        ]));

        $this->statusIdx = 0;
        for ($i = 1; $i <= 48; $i++) {
            Unit::create([
                'project_id'  => $project->id,
                'block_id'    => $block->id,
                'block'       => 'Phase 1',
                'unit_number' => "P-{$i}",
                'sort_order'  => $i - 1,
                'type'        => UnitType::Villa,
                'size_sqft'   => ($i % 4 === 0) ? 3500 : 2500,
                'price'       => ($i % 4 === 0) ? 8000000 : 6000000,
                'status'      => $this->nextStatus(),
            ]);
        }
    }

    // ── 6. Palm Breeze Townhouses ─────────────────────────────────
    private function palmBreezeTownhouses(Developer $dev, callable $f): void
    {
        $project = Project::create([
            'name'                => 'Palm Breeze Townhouses',
            'slug'                => 'palm-breeze-townhouses',
            'project_code'        => 'PBT-2025',
            'theme_color'         => '#475569',
            'developer_id'        => $dev->id,
            'type'                => ProjectType::Residential,
            'status'              => ProjectStatus::UnderConstruction,
            'location'            => 'Baridhara DOHS, Dhaka',
            'address'             => 'Road 2, Baridhara DOHS, Dhaka-1212',
            'description'         => 'Luxury townhouses in a secure gated enclave, featuring private rooftop terraces and smart home systems.',
            'start_date'          => '2024-06-01',
            'handover_date'       => '2026-12-31',
            'total_units'         => 16,
            'overall_progress'    => 42,
            'land_area'           => 32,
            'land_area_unit'      => 'katha',
            'estimated_value'     => 480000000,
            'booking_amount'      => 15,
            'booking_amount_type' => 'percentage',
            'commission_pct'      => 3.0,
            'latitude'            => 23.8010,
            'longitude'           => 90.4252,
        ]);

        $building = ProjectBuilding::create([
            'project_id'    => $project->id,
            'name'          => 'Row A',
            'specifications' => [
                'security'  => '24/7 Manned',
                'generator' => 'Full Backup',
            ],
            'sort_order' => 0,
        ]);

        $block = $building->sections()->create([
            'project_id'    => $project->id,
            'name'          => 'Townhouses',
            'type'          => 'villa',
            'planned_units' => 16,
            'sort_order'    => 0,
        ]);

        $project->facilities()->sync($f([
            'Swimming Pool', 'Smart Home System', 'Garden / Landscape Area',
            '24/7 Security', 'CCTV Surveillance', 'Visitor Parking',
            'Concierge Service', 'Rooftop Lounge',
        ]));

        $this->statusIdx = 0;
        for ($i = 1; $i <= 16; $i++) {
            Unit::create([
                'project_id'  => $project->id,
                'block_id'    => $block->id,
                'block'       => 'Row A',
                'unit_number' => "TH-{$i}",
                'sort_order'  => $i - 1,
                'type'        => UnitType::Townhouse,
                'bedrooms'    => 4,
                'size_sqft'   => 3200,
                'price'       => 32000000,
                'status'      => $this->nextStatus(),
            ]);
        }
    }

    // ── 7. Metro Warehouse ────────────────────────────────────────
    private function metroWarehouse(Developer $dev, callable $f): void
    {
        $project = Project::create([
            'name'             => 'Metro Warehouse Complex',
            'slug'             => 'metro-warehouse-complex',
            'project_code'     => 'MWC-2024',
            'theme_color'      => '#2A7DE1',
            'developer_id'     => $dev->id,
            'type'             => ProjectType::Commercial,
            'status'           => ProjectStatus::Completed,
            'location'         => 'Tongi Industrial Area, Gazipur',
            'address'          => 'Holding 55, Tongi I/A, Gazipur-1710',
            'description'      => 'Modern warehouse bays with dedicated truck docking, power backup, and 24/7 security — ideal for logistics and storage.',
            'total_units'      => 12,
            'overall_progress' => 100,
            'land_area'        => 60,
            'land_area_unit'   => 'katha',
            'estimated_value'  => 240000000,
            'commission_pct'   => 2.0,
            'latitude'         => 23.9081,
            'longitude'        => 90.4012,
        ]);

        $building = ProjectBuilding::create([
            'project_id'    => $project->id,
            'name'          => 'Bay Block A',
            'specifications' => [
                'service_lifts'  => 2,
                'parking_levels' => 1,
                'security'       => '24/7 Manned',
                'generator'      => 'Full Backup',
            ],
            'sort_order' => 0,
        ]);

        $block = $building->sections()->create([
            'project_id'    => $project->id,
            'name'          => 'Warehouse Bays',
            'type'          => 'industrial',
            'floor_start'   => 1,
            'floor_end'     => 1,
            'planned_units' => 12,
            'specifications' => ['cargo_access' => 'Dedicated Ramp'],
            'sort_order'    => 0,
        ]);

        $project->facilities()->sync($f([
            'Loading / Unloading Area', 'Electricity Backup', 'Generator',
            '24/7 Security', 'CCTV Surveillance', 'Waste Management',
        ]));

        $this->statusIdx = 0;
        for ($i = 1; $i <= 12; $i++) {
            Unit::create([
                'project_id'  => $project->id,
                'block_id'    => $block->id,
                'block'       => 'Bay Block A',
                'unit_number' => "W-{$i}",
                'floor'       => 1,
                'sort_order'  => $i - 1,
                'type'        => UnitType::CommercialSpace,
                'size_sqft'   => $i <= 8 ? 5000 : 3000,
                'price'       => $i <= 8 ? 18000000 : 12000000,
                'status'      => $this->nextStatus(),
            ]);
        }
    }
}
