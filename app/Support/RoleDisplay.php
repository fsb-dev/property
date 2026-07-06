<?php

namespace App\Support;

trait RoleDisplay
{
    /** Display color per role name, with a fallback for unseeded/custom roles. */
    private const ROLE_COLORS = [
        'super_admin'   => ['bg' => '#F1ECFF', 'color' => '#5B3DF5'],
        'company_admin' => ['bg' => '#E8F0FF', 'color' => '#3B82F6'],
        'sales_manager' => ['bg' => '#FFF3E0', 'color' => '#F59E0B'],
        'accountant'    => ['bg' => '#E6F7EE', 'color' => '#22C55E'],
        'site_engineer' => ['bg' => '#E0F2FE', 'color' => '#0EA5E9'],
    ];
    private const FALLBACK_COLOR = ['bg' => '#EEF1F6', 'color' => '#64748B'];

    private function roleColor(?string $name): array
    {
        return self::ROLE_COLORS[$name] ?? self::FALLBACK_COLOR;
    }

    private function roleLabel(?string $name): string
    {
        return $name ? str($name)->replace('_', ' ')->title()->toString() : 'Unassigned';
    }
}
