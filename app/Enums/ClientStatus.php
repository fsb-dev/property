<?php

namespace App\Enums;

enum ClientStatus: string
{
    case Active      = 'active';
    case Inactive    = 'inactive';
    case Blacklisted = 'blacklisted';

    public function label(): string
    {
        return match($this) {
            self::Active      => 'Active',
            self::Inactive    => 'Inactive',
            self::Blacklisted => 'Blacklisted',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active      => 'green',
            self::Inactive    => 'slate',
            self::Blacklisted => 'red',
        };
    }
}
