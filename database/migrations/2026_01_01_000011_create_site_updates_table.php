<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Latest Site Updates + Photo Timeline.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('media_path')->nullable();
            $table->string('media_type')->default('image'); // image | video
            $table->date('update_date');
            $table->timestamps();
            $table->index(['tenant_id', 'project_id', 'update_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_updates');
    }
};
