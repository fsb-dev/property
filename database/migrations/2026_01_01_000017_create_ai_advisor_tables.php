<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// OPTIONAL (good-to-have). Skip for the demo if time-boxed.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('kb_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('tags')->nullable();   // simple keyword matching for the demo
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->index(['tenant_id', 'is_published']);
        });

        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete(); // buyer chatting with Sara
            $table->string('title')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'client_id']);
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_conversation_id')->constrained()->cascadeOnDelete();
            $table->string('role');        // user | assistant
            $table->text('content');
            $table->timestamps();
            $table->index(['tenant_id', 'ai_conversation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
        Schema::dropIfExists('kb_articles');
    }
};
