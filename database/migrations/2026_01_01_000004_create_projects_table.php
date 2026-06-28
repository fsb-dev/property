<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();    // FK enforced when SaaS tenancy is implemented
            $table->unsignedBigInteger('developer_id')->nullable(); // FK enforced after developers table exists in same migration batch

            // Identity
            $table->string('name');
            $table->string('slug');
            $table->string('project_code', 20)->nullable()->unique(); // e.g. LVR-2026
            $table->string('theme_color', 7)->nullable();             // hex e.g. #5B3DF5
            $table->string('developer_name')->nullable();             // fallback when no developer record

            // Classification
            $table->string('type')->default('residential');
            $table->string('status')->default('draft');

            // Narrative
            $table->text('description')->nullable();

            // Geography
            $table->string('location')->nullable();    // Bashundhara R/A
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Timeline
            $table->date('start_date')->nullable();
            $table->date('handover_date')->nullable();

            // Area (denormalised summaries — recomputed from blocks on save)
            $table->unsignedSmallInteger('total_floors')->nullable();
            $table->unsignedSmallInteger('total_units')->nullable();
            $table->decimal('overall_progress', 5, 2)->default(0);

            // Land & build
            $table->decimal('land_area', 10, 2)->nullable();
            $table->string('land_area_unit', 20)->nullable();  // sqft | katha | acres | marla
            $table->decimal('built_up_area', 10, 2)->nullable();

            // Commercials
            $table->unsignedBigInteger('estimated_value')->nullable();   // BDT, whole number
            $table->decimal('booking_amount', 12, 2)->nullable();
            $table->string('booking_amount_type', 20)->nullable();       // fixed | percentage
            $table->decimal('commission_pct', 5, 2)->nullable();
            $table->unsignedSmallInteger('payment_plan_months')->nullable();
            $table->decimal('service_charge_sqft', 8, 2)->nullable();  // BDT per sqft per month — covers lift, cleaning, security
            $table->unsignedSmallInteger('maintenance_years')->nullable(); // post-handover maintenance included (years)

            // Catch-all for misc project-level specs
            $table->json('specifications')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
