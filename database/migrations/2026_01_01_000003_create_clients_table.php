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
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('nid')->nullable();       // national ID (KYC)
            $table->string('address')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('status')->default('active');
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['tenant_id', 'email']);  // email unique per tenant
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
