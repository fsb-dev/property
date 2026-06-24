<?php

namespace App\Enums;

enum ProjectCategory: string
{
    case Luxury    = 'luxury';
    case MidRange  = 'mid_range';
    case Affordable = 'affordable';

    public function label(): string
    {
        return match($this) {
            self::Luxury     => 'Luxury',
            self::MidRange   => 'Mid Range',
            self::Affordable => 'Affordable',
        };
    }
}
