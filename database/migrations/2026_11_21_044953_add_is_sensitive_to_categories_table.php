<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kategori "wajib ditangani khusus" (mis. Keamanan / pembulian).
     * Laporan pada kategori ini otomatis diklasifikasikan sensitif oleh sistem,
     * bukan oleh pelapor.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('is_sensitive')->default(false)->after('is_active');
        });

        // Kategori yang sudah ada: tandai yang berkaitan dengan keamanan & kesejahteraan
        // santri supaya aturan langsung berlaku tanpa perlu diatur manual dulu.
        DB::table('categories')
            ->whereIn('slug', ['keamanan'])
            ->update(['is_sensitive' => true]);
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('is_sensitive');
        });
    }
};
