<?php

namespace App\Enums;

enum ConstructionStatus: string
{
    case OnTrack    = 'on_track';
    case Delayed    = 'delayed';
    case Completed  = 'completed';
    case Inspection = 'inspection';
    case Paused     = 'paused';

    public function label(): string
    {
        return match ($this) {
            self::OnTrack    => 'On Track',
            self::Delayed    => 'Delayed',
            self::Completed  => 'Completed',
            self::Inspection => 'Inspection',
            self::Paused     => 'Paused',
        };
    }

    /** Tailwind badge variant used by the front-end Badge component. */
    public function variant(): string
    {
        return match ($this) {
            self::OnTrack    => 'success',
            self::Delayed    => 'destructive',
            self::Completed  => 'purple',
            self::Inspection => 'info',
            self::Paused     => 'warning',
        };
    }
}
