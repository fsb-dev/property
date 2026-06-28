<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            'Building Services' => [
                '24/7 Security', 'CCTV Surveillance', 'Security Guard', 'Fire Safety System',
                'Fire Exit', 'Emergency Alarm', 'Electricity Backup', 'Generator',
                'Water Supply', 'Waste Management', 'Maintenance Service', 'Cleaning Service',
                'Visitor Management', 'Parking',
            ],
            'Lifestyle & Wellness' => [
                'Swimming Pool', 'Gym / Fitness Center', 'Community Hall', "Children's Play Area",
                'Rooftop Garden', 'Garden / Landscape Area', 'Walking Track', 'Prayer Room / Mosque',
                'Indoor Games Room', 'Outdoor Sports Area', 'BBQ Area', 'Library',
                'Guest Room', 'Driver Room', 'Maid Room', 'Laundry Area',
            ],
            'Vertical Living' => [
                'Elevator / Lift', 'High-Speed Lift', 'Service Lift', 'Basement Parking',
                'Visitor Parking', 'Intercom System', 'Gas Supply', 'Solar Power',
                'Waste Disposal System', 'Reception / Lobby',
            ],
            'Business & Commerce' => [
                'Reception Area', 'Conference Room', 'Meeting Room', 'Food Court',
                'Retail Zone', 'ATM Booth', 'Banking Facilities', 'Business Lounge',
                'Loading / Unloading Area', 'Central AC', 'High-Speed Internet',
            ],
            'Mixed Use & Retail' => [
                'Shopping Mall', 'Restaurants', 'Cinema', 'Entertainment Zone',
                'Hotel Service', 'Retail Shops', 'Office Spaces', 'Parking Complex',
            ],
            'Land Development' => [
                'Internal Roads', 'Drainage System', 'Electricity Connection', 'Water Connection',
                'Boundary Wall', 'Security Gate', 'Club House', 'Community Center',
                'School Zone', 'Hospital Zone', 'Lake / Park',
            ],
            'Premium & Luxury' => [
                'Smart Home System', 'Private Pool', 'Concierge Service', 'Valet Parking',
                'Rooftop Lounge', 'Sky Garden', 'Private Elevator', 'Smart Security',
            ],
        ];

        $order = 0;
        foreach ($groups as $group => $items) {
            foreach ($items as $name) {
                Facility::firstOrCreate(['name' => $name], [
                    'group'      => $group,
                    'sort_order' => $order++,
                ]);
            }
        }
    }
}
