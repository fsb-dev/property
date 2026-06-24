<?php

namespace App\Enums;

enum UnitStatus: string
{
    case Available = 'available';
    case Reserved  = 'reserved';
    case Sold      = 'sold';

    public function label(): string
    {
        return match($this) {
            self::Available => 'Available',
            self::Reserved  => 'Reserved',
            self::Sold      => 'Sold',
        };
    }
}
