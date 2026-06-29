<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('block_id')->nullable();     // FK → project_blocks
            $table->unsignedSmallInteger('floor')->nullable();       // start floor
            $table->unsignedSmallInteger('floor_end')->nullable();    // end floor — set when unit spans multiple floors (block unit)
            $table->unsignedSmallInteger('sort_order')->default(0);   // left-to-right order in blueprint

            // ── Identity ──────────────────────────────────────────────────
            $table->string('unit_number');                          // A-1205  (auto-generated)
            $table->string('unit_code')->nullable();                // LVR-A-1205  (marketing code)
            $table->string('type')->nullable();                     // UnitType enum
            $table->string('wing')->nullable();                     // A, B, East, West …
            $table->string('block')->nullable();                    // legacy label
            $table->text('description')->nullable();

            // ── Specifications ────────────────────────────────────────────
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->unsignedTinyInteger('balconies')->nullable();   // count, not boolean
            $table->boolean('servant_room')->nullable();
            $table->boolean('store_room')->nullable();
            $table->unsignedTinyInteger('parking_spaces')->nullable();
            $table->string('facing_direction')->nullable();         // North, South, East, West …
            $table->string('view')->nullable();                     // Sea View, City View …

            // ── Measurements (all sqft, ceiling in ft) ────────────────────
            $table->unsignedInteger('size_sqft')->nullable();       // built-up area
            $table->unsignedInteger('super_built_up_area')->nullable();
            $table->unsignedInteger('carpet_area')->nullable();     // net / carpet area
            $table->decimal('ceiling_height', 4, 1)->nullable();    // e.g. 10.5 ft
            $table->unsignedInteger('terrace_area')->nullable();
            $table->unsignedInteger('parking_area')->nullable();

            // ── Pricing ───────────────────────────────────────────────────
            $table->decimal('price', 15, 2)->nullable();            // base / asking price
            $table->decimal('launch_price', 15, 2)->nullable();
            $table->decimal('current_price', 15, 2)->nullable();
            $table->decimal('parking_price', 15, 2)->nullable();
            $table->decimal('registration_fee', 15, 2)->nullable();
            $table->decimal('vat_pct', 5, 2)->nullable();
            $table->decimal('monthly_maintenance', 10, 2)->nullable();
            $table->decimal('booking_amount', 15, 2)->nullable();   // unit-level override

            // ── Availability ──────────────────────────────────────────────
            $table->string('status')->default('not_configured');
            $table->date('launch_date')->nullable();
            $table->date('available_date')->nullable();
            $table->date('handover_date')->nullable();

            // ── Media URLs (non-file, stored as strings) ─────────────────
            $table->string('video_url')->nullable();
            $table->string('tour_360_url')->nullable();

            // ── Mortgage ─────────────────────────────────────────────────
            $table->json('eligible_banks')->nullable();
            $table->decimal('max_loan_amount', 15, 2)->nullable();
            $table->unsignedSmallInteger('payment_plan_months')->nullable(); // unit-level override

            $table->timestamps();

            $table->index(['tenant_id', 'project_id', 'status']);
            $table->index(['block_id', 'floor', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
