<?php

namespace App\Enums;

enum ClientCountry: string
{
    case Bangladesh   = 'BD';
    case India        = 'IN';
    case Pakistan     = 'PK';
    case UnitedStates = 'US';
    case UnitedKingdom= 'GB';
    case UAE          = 'AE';
    case SaudiArabia  = 'SA';
    case Malaysia     = 'MY';
    case Singapore    = 'SG';
    case Australia    = 'AU';
    case Canada       = 'CA';
    case Other        = 'other';

    public function label(): string
    {
        return match($this) {
            self::Bangladesh    => 'Bangladesh',
            self::India         => 'India',
            self::Pakistan      => 'Pakistan',
            self::UnitedStates  => 'United States',
            self::UnitedKingdom => 'United Kingdom',
            self::UAE           => 'United Arab Emirates',
            self::SaudiArabia   => 'Saudi Arabia',
            self::Malaysia      => 'Malaysia',
            self::Singapore     => 'Singapore',
            self::Australia     => 'Australia',
            self::Canada        => 'Canada',
            self::Other         => 'Other',
        };
    }
}
