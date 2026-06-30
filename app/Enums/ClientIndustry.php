<?php

namespace App\Enums;

enum ClientIndustry: string
{
    case BankingFinance  = 'banking_finance';
    case Construction    = 'construction';
    case Education       = 'education';
    case Garments        = 'garments';
    case Government      = 'government';
    case Healthcare      = 'healthcare';
    case Hospitality     = 'hospitality';
    case IT              = 'it';
    case Manufacturing   = 'manufacturing';
    case Media           = 'media';
    case NGO             = 'ngo';
    case RealEstate      = 'real_estate';
    case RetailTrade     = 'retail_trade';
    case Transport       = 'transport';
    case Other           = 'other';

    public function label(): string
    {
        return match($this) {
            self::BankingFinance => 'Banking & Finance',
            self::Construction   => 'Construction',
            self::Education      => 'Education',
            self::Garments       => 'Garments & Textile',
            self::Government     => 'Government',
            self::Healthcare     => 'Healthcare',
            self::Hospitality    => 'Hospitality & Tourism',
            self::IT             => 'IT & Technology',
            self::Manufacturing  => 'Manufacturing',
            self::Media          => 'Media & Communications',
            self::NGO            => 'NGO / Non-Profit',
            self::RealEstate     => 'Real Estate',
            self::RetailTrade    => 'Retail & Trade',
            self::Transport      => 'Transport & Logistics',
            self::Other          => 'Other',
        };
    }
}
