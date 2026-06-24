<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Community & Future: nearby amenities + upcoming infrastructure + nearby projects (seed-driven).
return new class extends Migration {
    public function up(): void
    {
        Schema::create('area_developments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('kind')->default('amenity');   // amenity | infrastructure | nearby_project
            $table->string('category')->nullable();        // school | hospital | metro | commercial | park
            $table->string('name');
            $table->decimal('distance_km', 6, 2)->nullable();
            $table->string('status')->default('planned');  // planned | upcoming | approved | under_construction
            $table->date('completion_date')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'project_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_developments');
    }
};
