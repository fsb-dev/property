<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable(); // FK enforced when SaaS tenancy is implemented
            $table->string('name');                       // Lake View Residence
            $table->string('slug');
            $table->string('location')->nullable();       // Bashundhara R/A
            $table->string('address')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('type')->default('residential');
            $table->string('category')->nullable();
            $table->string('status')->default('under_construction');
            $table->unsignedSmallInteger('total_floors')->nullable();
            $table->unsignedSmallInteger('total_units')->nullable();
            $table->decimal('overall_progress', 5, 2)->default(0);   // mirrors milestone average
            $table->date('handover_date')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
