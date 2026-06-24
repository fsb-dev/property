<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// My Properties: Favorites + Compared (units a client saved but has not booked).
return new class extends Migration {
    public function up(): void
    {
        Schema::create('saved_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // favorite | compared
            $table->timestamps();
            $table->unique(['client_id', 'unit_id', 'type']);
            $table->index(['tenant_id', 'client_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_units');
    }
};
