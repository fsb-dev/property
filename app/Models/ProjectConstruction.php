<?php

namespace App\Models;

use App\Enums\ConstructionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectConstruction extends Model
{
    protected $fillable = [
        'tenant_id',
        'project_id',
        'status',
        'time_progress',
        'quality_score',
        'budget_total',
        'budget_used',
    ];

    protected $casts = [
        'status'        => ConstructionStatus::class,
        'time_progress' => 'decimal:2',
        'quality_score' => 'decimal:2',
        'budget_total'  => 'decimal:2',
        'budget_used'   => 'decimal:2',
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
