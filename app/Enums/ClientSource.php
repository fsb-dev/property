<?php

namespace App\Enums;

enum ClientSource: string
{
    case WalkIn     = 'walk_in';
    case Referral   = 'referral';
    case Website    = 'website';
    case Facebook   = 'facebook';
    case Phone      = 'phone';
    case Agent      = 'agent';
    case Exhibition = 'exhibition';
    case Other      = 'other';

    public function label(): string
    {
        return match($this) {
            self::WalkIn     => 'Walk-in',
            self::Referral   => 'Referral',
            self::Website    => 'Website',
            self::Facebook   => 'Facebook / Social',
            self::Phone      => 'Phone Call',
            self::Agent      => 'Agent',
            self::Exhibition => 'Exhibition / Event',
            self::Other      => 'Other',
        };
    }
}
