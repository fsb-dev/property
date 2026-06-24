<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// THE SPINE: a client's claim on one unit.
// One client -> many bookings -> each booking is one flat (same or different project).
return new class extends Migration {
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('reserved'); // reserved | purchased | cancelled | handed_over
            $table->decimal('price_agreed', 15, 2)->default(0); // the amount this client bought the flat for
            $table->date('booking_date')->nullable();
            $table->date('reserved_until')->nullable();     // reservation validity countdown
            $table->date('handover_date')->nullable();      // optional per-flat override (else use unit/project)
            $table->timestamps();
            $table->index(['tenant_id', 'client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
