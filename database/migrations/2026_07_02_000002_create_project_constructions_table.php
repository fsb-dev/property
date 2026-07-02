<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Per-project construction status summary — powers the Construction Updates
// dashboard (KPIs, status table) without recomputing heavy aggregates on every request.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_constructions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status')->default('on_track'); // on_track | delayed | completed | inspection | paused
            $table->decimal('time_progress', 5, 2)->default(0);  // schedule-based expected progress %
            $table->decimal('quality_score', 5, 2)->default(0);  // 0-100 inspection quality score
            $table->decimal('budget_total', 14, 2)->nullable();
            $table->decimal('budget_used', 14, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_constructions');
    }
};
