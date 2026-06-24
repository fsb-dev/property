<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// `users` = ADMIN/STAFF only (web guard). Buyers live in `clients`.
// Extends Laravel's default users table; does not recreate it.
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('company_admin')->after('email'); // super_admin | company_admin | staff
            $table->string('phone')->nullable()->after('role');
            $table->string('avatar_path')->nullable()->after('phone');
            $table->index(['tenant_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropColumn(['role', 'phone', 'avatar_path']);
        });
    }
};
