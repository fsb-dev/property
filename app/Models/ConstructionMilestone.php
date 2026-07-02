<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConstructionMilestone extends Model
{
    protected $fillable = [
        'tenant_id',
        'project_id',
        'name',
        'description',
        'progress',
        'status',
        'sort_order',
        'estimated_date',
        'completed_date',
    ];

    protected $casts = [
        'progress'       => 'decimal:2',
        'estimated_date' => 'date',
        'completed_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
