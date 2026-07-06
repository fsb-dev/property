<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles & permissions first
        $this->call(RolesAndPermissionsSeeder::class);

        // Demo super admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@homeverse.com'],
            [
                'name'     => 'Super Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('super_admin');

        $this->call(FacilitySeeder::class);
        $this->call(ProjectSeeder::class);
        $this->call(ConstructionSeeder::class);
        $this->call(ClientSeeder::class);
        $this->call(BookingSeeder::class);
    }
}
