<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Investment Analyzer inputs (one row per project). Math computed in the service.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_market_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->decimal('price_per_sqft', 12, 2)->nullable();
            $table->decimal('avg_rental_income', 12, 2)->nullable();  // per month
            $table->decimal('annual_expenses', 12, 2)->nullable();
            $table->decimal('best_case_return', 5, 2)->nullable();    // annual %
            $table->decimal('expected_case_return', 5, 2)->nullable();
            $table->decimal('conservative_return', 5, 2)->nullable();
            $table->json('price_history')->nullable();   // [{year:2021, price:8000}, ...]
            $table->timestamps();
            $table->index(['tenant_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_market_stats');
    }
};
