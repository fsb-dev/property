<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Draft              = 'draft';
    case Planning           = 'planning';
    case UnderConstruction  = 'under_construction';
    case Completed          = 'completed';
    case OnHold             = 'on_hold';
    case Cancelled          = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Draft             => 'Draft',
            self::Planning          => 'Planning',
            self::UnderConstruction => 'Under Construction',
            self::Completed         => 'Completed',
            self::OnHold            => 'On Hold',
            self::Cancelled         => 'Cancelled',
        };
    }
}
