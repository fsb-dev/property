<?php

namespace App\Enums;

enum UnitStatus: string
{
    case NotConfigured = 'not_configured'; // generated, no specs yet
    case Configured    = 'configured';     // specs filled, not listed for sale
    case Available     = 'available';      // listed, ready to buy
    case Booked        = 'booked';         // reserved by a customer
    case Sold          = 'sold';           // deal closed

    public function label(): string
    {
        return match($this) {
            self::NotConfigured => 'Not Configured',
            self::Configured    => 'Configured',
            self::Available     => 'Available',
            self::Booked        => 'Booked',
            self::Sold          => 'Sold',
        };
    }

    /** Tailwind color token used in blueprint UI */
    public function color(): string
    {
        return match($this) {
            self::NotConfigured => 'orange',
            self::Configured    => 'blue',
            self::Available     => 'green',
            self::Booked        => 'red',
            self::Sold          => 'purple',
        };
    }

    /** Sales pipeline statuses — visible to customers / agents */
    public function isSaleStatus(): bool
    {
        return in_array($this, [self::Available, self::Booked, self::Sold]);
    }
}
