<?php

namespace App\Enums;

enum ProjectType: string
{
    case Residential = 'residential';
    case Commercial  = 'commercial';
    case MixedUse    = 'mixed_use';
    case Land        = 'land';
    case Industrial  = 'industrial';

    public function label(): string
    {
        return match($this) {
            self::Residential => 'Residential',
            self::Commercial  => 'Commercial',
            self::MixedUse    => 'Mixed Use',
            self::Land        => 'Land / Plot',
            self::Industrial  => 'Industrial',
        };
    }

    /** @return ProjectCategory[] */
    public function categories(): array
    {
        return array_values(array_filter(
            ProjectCategory::cases(),
            fn(ProjectCategory $c) => $c->type() === $this
        ));
    }
}
