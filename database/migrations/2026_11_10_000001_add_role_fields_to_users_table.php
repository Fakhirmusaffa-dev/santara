<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('pelapor')->after('password');
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('phone', 30)->nullable()->after('is_active');
            $table->string('position', 100)->nullable()->after('phone');
            $table->string('avatar_path', 255)->nullable()->after('position');
            $table->timestamp('last_login_at')->nullable()->after('avatar_path');
            $table->index(['role', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'is_active']);
            $table->dropColumn(['role', 'is_active', 'phone', 'position', 'avatar_path', 'last_login_at']);
        });
    }
};
