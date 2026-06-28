<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Statutory approvals and certifications per project.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_compliances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');                             // Building Permit, Fire Safety NOC
            $table->string('type')->default('approval');        // approval | certification
            $table->string('status')->default('pending');       // pending | obtained | not_required
            $table->date('obtained_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_compliances');
    }
};
