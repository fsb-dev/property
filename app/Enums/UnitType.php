<?php

namespace App\Enums;

enum UnitType: string
{
    // ── Residential ───────────────────────────────────────────
    case Studio    = 'studio';
    case Apartment = 'apartment';
    case Penthouse = 'penthouse';
    case Duplex    = 'duplex';
    case Villa     = 'villa';
    case Townhouse = 'townhouse';

    // ── Commercial ────────────────────────────────────────────
    case Shop            = 'shop';
    case Office          = 'office';
    case CommercialSpace = 'commercial_space';

    // ── Industrial ────────────────────────────────────────────
    case Warehouse = 'warehouse';

    public function label(): string
    {
        return match($this) {
            self::Studio         => 'Studio',
            self::Apartment      => 'Apartment',
            self::Penthouse      => 'Penthouse',
            self::Duplex         => 'Duplex',
            self::Villa          => 'Villa',
            self::Townhouse      => 'Townhouse',
            self::Shop           => 'Shop',
            self::Office         => 'Office',
            self::CommercialSpace=> 'Commercial Space',
            self::Warehouse      => 'Warehouse',
        };
    }

    /** Unit types available for a given section type — keeps dropdown contextual. */
    public static function forSectionType(string $sectionType): array
    {
        return match($sectionType) {
            'residential' => [self::Apartment, self::Studio, self::Penthouse, self::Duplex],
            'villa'       => [self::Villa, self::Townhouse, self::Duplex],
            'commercial'  => [self::Shop, self::CommercialSpace],
            'office'      => [self::Office],
            'industrial'  => [self::Warehouse],
            default       => self::cases(), // mixed or unknown — show all
        };
    }
}
