<?php

namespace App\Enums;

class ProjectFacilities
{
    const COMMON = [
        '24/7 Security',
        'CCTV Surveillance',
        'Security Guard',
        'Reception / Lobby',
        'Electricity Backup',
        'Generator',
        'Water Supply',
        'Fire Safety System',
        'Fire Exit',
        'Emergency Alarm',
        'Maintenance Service',
        'Cleaning Service',
        'Waste Management',
        'Visitor Management',
        'Parking',
    ];

    const RESIDENTIAL = [
        'Swimming Pool',
        'Gym / Fitness Center',
        'Community Hall',
        "Children's Play Area",
        'Rooftop Garden',
        'Garden / Landscape Area',
        'Walking Track',
        'Prayer Room / Mosque',
        'Indoor Games Room',
        'Outdoor Sports Area',
        'BBQ Area',
        'Library',
        'Guest Room',
        'Driver Room',
        'Maid Room',
        'Laundry Area',
    ];

    const APARTMENT = [
        'Elevator / Lift',
        'High-Speed Lift',
        'Service Lift',
        'Basement Parking',
        'Visitor Parking',
        'Intercom System',
        'Gas Supply',
        'Solar Power',
        'Waste Disposal System',
    ];

    const COMMERCIAL = [
        'Reception Area',
        'Conference Room',
        'Meeting Room',
        'Food Court',
        'Retail Zone',
        'ATM Booth',
        'Banking Facilities',
        'Business Lounge',
        'Loading / Unloading Area',
        'Central AC',
        'High-Speed Internet',
    ];

    const MIXED_USE = [
        'Shopping Mall',
        'Restaurants',
        'Cinema',
        'Entertainment Zone',
        'Hotel Service',
        'Retail Shops',
        'Office Spaces',
        'Parking Complex',
    ];

    const LAND = [
        'Internal Roads',
        'Drainage System',
        'Electricity Connection',
        'Water Connection',
        'Boundary Wall',
        'Security Gate',
        'Club House',
        'Community Center',
        'School Zone',
        'Hospital Zone',
        'Lake / Park',
    ];

    const LUXURY = [
        'Smart Home System',
        'Private Pool',
        'Concierge Service',
        'Valet Parking',
        'Rooftop Lounge',
        'Sky Garden',
        'Private Elevator',
        'Smart Security',
    ];
}
