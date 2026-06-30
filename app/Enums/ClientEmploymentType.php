<?php

namespace App\Enums;

enum ClientEmploymentType: string
{
    case Salaried     = 'salaried';
    case SelfEmployed = 'self_employed';
    case Business     = 'business';
    case Freelancer   = 'freelancer';
    case Retired      = 'retired';
    case Unemployed   = 'unemployed';
    case Other        = 'other';

    public function label(): string
    {
        return match($this) {
            self::Salaried     => 'Salaried',
            self::SelfEmployed => 'Self Employed',
            self::Business     => 'Business Owner',
            self::Freelancer   => 'Freelancer',
            self::Retired      => 'Retired',
            self::Unemployed   => 'Unemployed',
            self::Other        => 'Other',
        };
    }
}
