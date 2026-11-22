<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Role `petugas` diganti dua koordinator:
 *   - koordinator_keamanan : menerima laporan keamanan (mis. pembulian)
 *   - koordinator_logistik : menerima laporan sarana & prasarana
 *
 * Pemetaan laporan -> koordinator disimpan di `categories.koordinator_role`,
 * supaya admin bisa mengubahnya tanpa menyentuh kode.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Nullable: kategori tanpa koordinator tetap lewat jalur admin (validasi manual).
            $table->string('koordinator_role', 40)->nullable()->after('is_sensitive');
        });

        // Kategori keamanan -> koordinator keamanan.
        DB::table('categories')
            ->where('slug', 'like', '%keamanan%')
            ->orWhere('is_sensitive', true)
            ->update(['koordinator_role' => 'koordinator_keamanan']);

        // Sarana & prasarana -> koordinator logistik.
        DB::table('categories')
            ->where('slug', 'like', '%sarana%')
            ->orWhere('slug', 'like', '%prasarana%')
            ->update(['koordinator_role' => 'koordinator_logistik']);

        // Data lama: pindahkan pemilik role `petugas` ke divisi yang paling masuk akal
        // berdasarkan jabatannya, agar akun mereka tidak kehilangan akses.
        // (Dikelompokkan dalam closure supaya `orWhere` tidak memakan kondisi role.)
        DB::table('users')->where('role', 'petugas')->update(['role' => 'koordinator_logistik']);

        DB::table('users')
            ->where('role', 'koordinator_logistik')
            ->where(function ($q) {
                $q->where('position', 'like', '%keamanan%')
                    ->orWhere('name', 'like', '%keamanan%');
            })
            ->update(['role' => 'koordinator_keamanan']);
    }

    public function down(): void
    {
        DB::table('users')
            ->whereIn('role', ['koordinator_keamanan', 'koordinator_logistik'])
            ->update(['role' => 'petugas']);

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('koordinator_role');
        });
    }
};
