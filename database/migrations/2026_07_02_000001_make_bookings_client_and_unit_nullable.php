<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// "Save Draft" persists an in-progress reservation as a bookings row with
// status=draft, before a buyer or unit has necessarily been picked yet.
return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->change();
            $table->foreignId('unit_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable(false)->change();
            $table->foreignId('unit_id')->nullable(false)->change();
        });
    }
};
