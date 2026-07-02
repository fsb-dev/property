<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Draft      = 'draft';
    case Reserved   = 'reserved';
    case Purchased  = 'purchased';
    case Cancelled  = 'cancelled';
    case HandedOver = 'handed_over';

    public function label(): string
    {
        return match($this) {
            self::Draft      => 'Draft',
            self::Reserved   => 'Reserved',
            self::Purchased  => 'Sold',
            self::Cancelled  => 'Cancelled',
            self::HandedOver => 'Handed Over',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft      => 'slate',
            self::Reserved   => 'amber',
            self::Purchased  => 'green',
            self::Cancelled  => 'red',
            self::HandedOver => 'purple',
        };
    }
}
