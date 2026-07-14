<?php

namespace App\Support;

trait RoleDisplay
{
    /** Display color per role name, with a fallback for unseeded/custom roles. */
    private const ROLE_COLORS = [
        'super_admin'   => ['bg' => 'rgba(198,161,91,0.12)', 'color' => '#C6A15B'],
        'company_admin' => ['bg' => 'rgba(96,165,250,0.15)', 'color' => '#60A5FA'],
        'sales_manager' => ['bg' => 'rgba(251,191,36,0.15)', 'color' => '#FBBF24'],
        'accountant'    => ['bg' => 'rgba(52,211,153,0.15)', 'color' => '#34D399'],
        'site_engineer' => ['bg' => 'rgba(34,211,238,0.15)', 'color' => '#22D3EE'],
    ];
    private const FALLBACK_COLOR = ['bg' => 'rgba(138,135,128,0.15)', 'color' => '#8A8780'];

    private function roleColor(?string $name): array
    {
        return self::ROLE_COLORS[$name] ?? self::FALLBACK_COLOR;
    }

    private function roleLabel(?string $name): string
    {
        return $name ? str($name)->replace('_', ' ')->title()->toString() : 'Unassigned';
    }
}
