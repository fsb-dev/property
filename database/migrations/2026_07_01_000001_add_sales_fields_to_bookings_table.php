<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Sales-workspace fields for the Bookings demo module — layered on top of
// the existing bookings "spine" without touching it.
return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('sales_rep_id')->nullable()->after('client_id')->constrained('users')->nullOnDelete();
            $table->string('source')->nullable()->after('status');
            $table->string('priority')->default('normal')->after('source');
            $table->decimal('discount_pct', 5, 2)->nullable()->after('price_agreed');
            $table->text('notes')->nullable()->after('handover_date');
            $table->json('meta')->nullable()->after('notes'); // demo-only: mortgage, documents, approvals, agreement
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sales_rep_id');
            $table->dropColumn(['source', 'priority', 'discount_pct', 'notes', 'meta']);
        });
    }
};
