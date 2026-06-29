<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectBlock extends Model
{
    protected $fillable = [
        'project_id',
        'building_id',
        'name',
        'type',
        'floor_start',
        'floor_end',
        'planned_units',
        'is_block_unit',
        'specifications',
        'sort_order',
    ];

    protected $casts = [
        'specifications' => 'array',
        'floor_start'    => 'integer',
        'floor_end'      => 'integer',
        'planned_units'  => 'integer',
        'is_block_unit'  => 'boolean',
        'sort_order'     => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(ProjectBuilding::class, 'building_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class, 'block_id');
    }
}
