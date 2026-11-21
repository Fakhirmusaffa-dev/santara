<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('reporter_name', 120)->nullable()->after('user_id');
            $table->string('reporter_contact', 60)->nullable()->after('reporter_name');
            $table->boolean('is_anonymous')->default(false)->after('reporter_contact');

            $table->index(['reporter_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropIndex(['reporter_name']);
            $table->dropColumn(['reporter_name', 'reporter_contact', 'is_anonymous']);
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
