<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Two-level building structure:
//   project_buildings → the physical structure (Tower A, Tower B)
//   project_blocks    → a floor-range section within a building (Retail floors 1-2, Apartments floors 3-15)
return new class extends Migration {
    public function up(): void
    {
        // Physical buildings within a project
        Schema::create('project_buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');                          // Tower A, Tower B, Block C, Cluster A
            $table->unsignedSmallInteger('total_floors')->nullable();
            $table->json('specifications')->nullable();      // shared: lifts, parking, lobby, security, generator
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'sort_order']);
        });

        // Floor-range sections inside a building (one building can have multiple sections with different types)
        Schema::create('project_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('building_id')->nullable(); // no FK constraint — buildings migration may run after
            $table->string('name')->nullable();              // Retail Zone, Apartments, Penthouse Level
            $table->string('type')->default('residential');  // residential | villa | commercial | office | industrial | mixed
            $table->unsignedSmallInteger('floor_start')->nullable();
            $table->unsignedSmallInteger('floor_end')->nullable();
            $table->unsignedSmallInteger('planned_units')->nullable();
            $table->json('specifications')->nullable();      // section-specific: HVAC, cargo_access, internet, electrical
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'sort_order']);
            $table->index(['building_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_blocks');
        Schema::dropIfExists('project_buildings');
    }
};
