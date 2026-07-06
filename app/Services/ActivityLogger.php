<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogger
{
    public function log(string $description): void
    {
        ActivityLog::create([
            'causer_id'   => auth()->id(),
            'description' => $description,
        ]);
    }

    public function recent(int $limit = 20): array
    {
        return ActivityLog::with('causer')->latest()->limit($limit)->get()
            ->map(fn (ActivityLog $log) => [
                'title' => $log->description,
                'desc'  => $log->causer ? 'by '.$log->causer->name : 'System',
                'time'  => $log->created_at->diffForHumans(),
            ])
            ->toArray();
    }
}
