<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectCompliance extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'type',
        'status',
        'obtained_date',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'obtained_date' => 'date',
        'expiry_date'   => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
