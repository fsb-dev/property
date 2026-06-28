<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Master list of amenities / facilities that can be assigned to projects via project_facility pivot.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('group');  // Building Services | Lifestyle & Wellness | Vertical Living | Business & Commerce | Premium & Luxury | Land Development
            $table->string('icon')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('group');
        });

        Schema::create('project_facility', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->primary(['project_id', 'facility_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_facility');
        Schema::dropIfExists('facilities');
    }
};
