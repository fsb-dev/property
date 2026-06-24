<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Actual money received (simulated gateway or admin-recorded). Settles installments.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('installment_id')->nullable()->constrained()->nullOnDelete(); // null = ad-hoc/extra
            $table->decimal('amount', 15, 2);
            $table->string('method')->default('bank_transfer'); // bkash | nagad | card | bank_transfer | cash
            $table->string('reference')->nullable();            // gateway/txn ref (simulated)
            $table->string('status')->default('completed');     // pending | completed | failed
            $table->timestamp('paid_at')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
