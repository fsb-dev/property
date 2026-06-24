<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Planning           = 'planning';
    case UnderConstruction  = 'under_construction';
    case Completed          = 'completed';

    public function label(): string
    {
        return match($this) {
            self::Planning          => 'Planning',
            self::UnderConstruction => 'Under Construction',
            self::Completed         => 'Completed',
        };
    }
}
