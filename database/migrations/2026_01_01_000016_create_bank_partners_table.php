<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Mortgage page: banking partners list.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');                 // BRAC Bank
            $table->string('logo_path')->nullable();
            $table->decimal('interest_rate_from', 5, 2)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_partners');
    }
};
