<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectBuilding extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'total_floors',
        'specifications',
        'sort_order',
    ];

    protected $casts = [
        'specifications' => 'array',
        'total_floors'   => 'integer',
        'sort_order'     => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ProjectBlock::class, 'building_id')->orderBy('sort_order');
    }
}
