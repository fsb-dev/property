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

            // ── Step 1: Personal Information ──────────────────────────────
            $table->string('name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable()->default('Bangladeshi');

            // ── Step 2: Contact Details ───────────────────────────────────
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('phone')->nullable();
            $table->string('alternate_phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();

            // ── Step 3: National ID / Passport ────────────────────────────
            $table->string('nid')->nullable();
            $table->string('birth_certificate_number')->nullable();
            $table->string('passport_no')->nullable();
            $table->date('passport_expiry')->nullable();

            // ── Step 4: Address ───────────────────────────────────────────
            $table->text('address')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->text('permanent_address')->nullable();

            // ── Step 5: Employment ────────────────────────────────────────
            $table->string('occupation')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('company')->nullable();
            $table->string('designation')->nullable();
            $table->string('industry')->nullable();
            $table->string('office_address')->nullable();
            $table->string('tenure')->nullable();

            // ── Step 6: Income & Financials ───────────────────────────────
            $table->string('monthly_income')->nullable();
            $table->string('annual_income')->nullable();
            $table->string('other_income')->nullable();
            $table->string('existing_loans')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();

            // ── Step 7: Co-applicant ──────────────────────────────────────
            $table->string('coapplicant_name')->nullable();
            $table->string('coapplicant_relationship')->nullable();
            $table->date('coapplicant_dob')->nullable();
            $table->string('coapplicant_phone')->nullable();
            $table->string('coapplicant_email')->nullable();
            $table->string('coapplicant_nid')->nullable();
            $table->string('coapplicant_occupation')->nullable();
            $table->string('coapplicant_monthly_income')->nullable();
            $table->string('coapplicant_annual_income')->nullable();
            $table->string('coapplicant_tin')->nullable();
            $table->string('coapplicant_ownership_percentage')->nullable();
            $table->text('coapplicant_address')->nullable();

            // ── Meta ──────────────────────────────────────────────────────
         
            $table->text('notes')->nullable();
            $table->string('status')->default('active');
            $table->rememberToken();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
