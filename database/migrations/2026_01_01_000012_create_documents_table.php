<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Metadata only for the demo (no real storage binding yet).
return new class extends Migration {
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();   // owning buyer
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('category')->default('other'); // legal | payments | construction | other
            $table->string('file_type')->nullable();      // pdf | xls | img
            $table->string('file_path')->nullable();       // wired later
            $table->unsignedBigInteger('file_size')->nullable(); // bytes, for "Storage Used"
            $table->string('status')->default('received'); // signed | pending | verified | received | approved | latest
            $table->boolean('requires_signature')->default(false);
            $table->date('expires_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'category', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
