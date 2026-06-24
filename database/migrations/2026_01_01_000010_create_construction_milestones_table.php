<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('construction_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');                    // Foundation Work
            $table->string('description')->nullable(); // Excavation & Foundation
            $table->decimal('progress', 5, 2)->default(0); // 0-100
            $table->string('status')->default('not_started'); // completed | in_progress | pending | not_started
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->date('estimated_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'project_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('construction_milestones');
    }
};
