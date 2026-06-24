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
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('unit_number');           // A-1205
            $table->string('block')->nullable();     // Block A
            $table->unsignedSmallInteger('floor')->nullable();
            $table->string('type')->nullable();      // 2 Bed Apartment
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedInteger('size_sqft')->nullable();
            $table->string('view')->nullable();      // Lake View
            $table->decimal('price', 15, 2)->default(0);
            $table->string('status')->default('available'); // available | reserved | sold
            $table->date('handover_date')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
