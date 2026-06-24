<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// One plan per booking (per flat).
return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('plan_type')->default('construction_linked'); // construction_linked | equal_monthly | custom
            $table->decimal('total_amount', 15, 2);
            $table->decimal('down_payment', 15, 2)->default(0);
            $table->date('start_date');
            $table->date('handover_date')->nullable();
            $table->unsignedSmallInteger('total_installments');
            $table->unsignedSmallInteger('duration_months')->nullable();
            $table->string('status')->default('active'); // active | completed | cancelled
            $table->timestamps();
            $table->index(['tenant_id', 'booking_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_plans');
    }
};
