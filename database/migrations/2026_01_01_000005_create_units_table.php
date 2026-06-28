<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// A flat inside a project (the sellable asset).
return new class extends Migration {
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('block_id')->nullable(); // FK → project_blocks; no constraint — avoids migration ordering issue
            $table->string('unit_number');                      // A-1205
            $table->string('block')->nullable();                // legacy label — kept until block_id fully replaces it
            $table->unsignedSmallInteger('floor')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0); // left-to-right circle order in blueprint UI
            $table->string('type')->nullable();
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedInteger('size_sqft')->nullable();
            $table->string('view')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->string('status')->default('available');
            $table->date('handover_date')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'project_id', 'status']);
            $table->index(['block_id', 'floor', 'sort_order']); // blueprint canvas query
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
