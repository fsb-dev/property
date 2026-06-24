<?php

namespace App\Enums;

enum UnitType: string
{
    case Studio       = 'studio';
    case OneBed       = '1_bed';
    case TwoBed       = '2_bed';
    case ThreeBed     = '3_bed';
    case FourBed      = '4_bed';
    case Penthouse    = 'penthouse';
    case Duplex       = 'duplex';
    case CommercialSpace = 'commercial_space';

    public function label(): string
    {
        return match($this) {
            self::Studio          => 'Studio',
            self::OneBed          => '1 Bedroom',
            self::TwoBed          => '2 Bedroom',
            self::ThreeBed        => '3 Bedroom',
            self::FourBed         => '4 Bedroom',
            self::Penthouse       => 'Penthouse',
            self::Duplex          => 'Duplex',
            self::CommercialSpace => 'Commercial Space',
        };
    }
}
