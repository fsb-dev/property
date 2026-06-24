<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permissions ────────────────────────────────────────────────
        $permissions = [
            // Projects
            'view projects',   'create projects',   'edit projects',   'delete projects',

            // Units
            'view units',      'create units',      'edit units',      'delete units',

            // Clients
            'view clients',    'create clients',    'edit clients',    'delete clients',

            // Bookings
            'view bookings',   'create bookings',   'edit bookings',   'delete bookings',

            // Payments & Installments
            'view payments',   'create payments',   'edit payments',   'delete payments',

            // Construction
            'view construction', 'manage construction',

            // Documents
            'view documents',  'upload documents',  'delete documents',

            // Reports
            'view reports',

            // Settings (admin-level config)
            'manage settings',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── Roles ──────────────────────────────────────────────────────

        // super_admin — no permissions in DB; Gate::before() in AppServiceProvider
        // returns true for every check, so super_admin is a wildcard automatically.
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        // company_admin — everything except system settings
        $companyAdmin = Role::firstOrCreate(['name' => 'company_admin', 'guard_name' => 'web']);
        $companyAdmin->syncPermissions(array_diff($permissions, ['manage settings']));

        // sales_manager — clients, bookings, units, projects (view only)
        $salesManager = Role::firstOrCreate(['name' => 'sales_manager', 'guard_name' => 'web']);
        $salesManager->syncPermissions([
            'view projects',
            'view units',
            'view clients',    'create clients',   'edit clients',
            'view bookings',   'create bookings',  'edit bookings',
            'view payments',
            'view documents',  'upload documents',
            'view reports',
        ]);

        // accountant — payments, reports, view others
        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $accountant->syncPermissions([
            'view projects',
            'view units',
            'view clients',
            'view bookings',
            'view payments',   'create payments',  'edit payments',
            'view documents',
            'view reports',
        ]);

        // site_engineer — construction updates only
        $siteEngineer = Role::firstOrCreate(['name' => 'site_engineer', 'guard_name' => 'web']);
        $siteEngineer->syncPermissions([
            'view projects',
            'view construction', 'manage construction',
            'view documents',    'upload documents',
        ]);
    }
}
