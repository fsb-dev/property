<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// `clients` = BUYERS. Own auth identity for the client portal (client guard).
// Separate from `users` so the two portals never share an identity space.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('phone')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable()->default('Bangladeshi');
            $table->string('nid')->nullable();
            $table->string('passport_no')->nullable();
            $table->string('occupation')->nullable();
            $table->text('address')->nullable();
            $table->string('source')->nullable();   // walk_in, referral, website, etc.
            $table->text('notes')->nullable();
            $table->string('status')->default('active');
            $table->rememberToken();
            $table->timestamps();

            $table->index('status');
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
