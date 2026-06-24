<?php

namespace App\Enums;

enum ProjectType: string
{
    case Residential = 'residential';
    case Commercial  = 'commercial';
    case MixedUse    = 'mixed_use';
    case Villa       = 'villa';
    case Plot        = 'plot';

    public function label(): string
    {
        return match($this) {
            self::Residential => 'Residential',
            self::Commercial  => 'Commercial',
            self::MixedUse    => 'Mixed Use',
            self::Villa       => 'Villa / Townhouse',
            self::Plot        => 'Plot / Land',
        };
    }
}
