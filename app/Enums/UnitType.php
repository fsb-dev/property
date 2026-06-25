<?php

namespace App\Enums;

enum UnitType: string
{
    // ── Residential ───────────────────────────────────────────
    case Studio    = 'studio';
    case OneBed    = '1_bed';
    case TwoBed    = '2_bed';
    case ThreeBed  = '3_bed';
    case FourBed   = '4_bed';
    case FiveBed   = '5_bed';
    case Penthouse = 'penthouse';
    case Duplex    = 'duplex';
    case Villa     = 'villa';
    case Townhouse = 'townhouse';

    // ── Commercial ────────────────────────────────────────────
    case Shop            = 'shop';
    case Office          = 'office';
    case CommercialSpace = 'commercial_space';

    public function label(): string
    {
        return match($this) {
            self::Studio         => 'Studio',
            self::OneBed         => '1 Bedroom',
            self::TwoBed         => '2 Bedroom',
            self::ThreeBed       => '3 Bedroom',
            self::FourBed        => '4 Bedroom',
            self::FiveBed        => '5 Bedroom',
            self::Penthouse      => 'Penthouse',
            self::Duplex         => 'Duplex',
            self::Villa          => 'Villa',
            self::Townhouse      => 'Townhouse',
            self::Shop           => 'Shop',
            self::Office         => 'Office',
            self::CommercialSpace=> 'Commercial Space',
        };
    }

    /**
     * Controls which locator fields appear in the unit form.
     * showBlock / showFloor: visibility
     * blockLabel / floorLabel: dynamic labels
     * showBedrooms: bedrooms field visibility
     */
    public function formConfig(): array
    {
        return match($this) {
            self::Villa => [
                'showBlock'    => true,
                'blockLabel'   => 'Phase',
                'showFloor'    => false,
                'floorLabel'   => 'Floor',
                'showBedrooms' => true,
            ],
            self::Townhouse => [
                'showBlock'    => true,
                'blockLabel'   => 'Phase / Row',
                'showFloor'    => false,
                'floorLabel'   => 'Floor',
                'showBedrooms' => true,
            ],
            self::Duplex => [
                'showBlock'    => true,
                'blockLabel'   => 'Block',
                'showFloor'    => true,
                'floorLabel'   => 'Starting Floor',
                'showBedrooms' => true,
            ],
            self::Shop,
            self::Office,
            self::CommercialSpace => [
                'showBlock'    => false,
                'blockLabel'   => 'Block',
                'showFloor'    => true,
                'floorLabel'   => 'Level',
                'showBedrooms' => false,
            ],
            default => [
                'showBlock'    => true,
                'blockLabel'   => 'Block',
                'showFloor'    => true,
                'floorLabel'   => 'Floor',
                'showBedrooms' => true,
            ],
        };
    }
}
