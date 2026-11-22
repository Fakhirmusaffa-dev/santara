<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Mengarahkan laporan baru LANGSUNG ke koordinator divisinya, tanpa antre di admin.
 *
 *   kategori -> categories.koordinator_role -> role koordinator aktif
 *     "Keamanan"             -> koordinator_keamanan
 *     "Sarana"/"prasarana"   -> koordinator_logistik
 *
 * Satu divisi bisa punya lebih dari satu koordinator aktif; semuanya menerima
 * distribusi laporan (yang pertama tercatat sebagai `assigned_to`, sisanya
 * punya baris penugasan sendiri sehingga "Tugas Saya" mereka ikut terisi).
 *
 * Laporan sensitif (mis. pembulian) TETAP dirutekan ke koordinator keamanan,
 * tetapi akses isinya tetap dikunci policy need-to-know; admin hanya diberi
 * tembusan metadata untuk pengawasan, bukan isi laporan.
 */
class ComplaintRouter
{
    /**
     * Koordinator penerima laporan ini (semua koordinator aktif divisinya).
     *
     * @return Collection<int, User>
     */
    public function recipientsFor(Complaint $complaint): Collection
    {
        return $this->recipientsForRole($this->roleFor($complaint));
    }

    /**
     * Koordinator aktif untuk sebuah role divisi.
     *
     * @return Collection<int, User>
     */
    public function recipientsForRole(?string $role): Collection
    {
        if ($role === null) {
            return collect();
        }

        return User::koordinatorFor($role);
    }

    /** Role koordinator tujuan berdasarkan kategori laporan. */
    public function roleFor(Complaint $complaint): ?string
    {
        return self::normalizeRole($complaint->category?->koordinator_role);
    }

    /** Apakah laporan ini punya koordinator tujuan (bila tidak, jatuh ke admin). */
    public function isRoutable(Complaint $complaint): bool
    {
        return $this->recipientsFor($complaint)->isNotEmpty();
    }

    /**
     * Tembusan pengawasan: semua admin & pimpinan aktif. Mereka boleh melihat
     * laporan non-sensitif secara penuh untuk memantau dan mengoordinasi;
     * laporan sensitif hanya berupa metadata sampai admin memberi akses.
     *
     * @return Collection<int, User>
     */
    public function watchers(): Collection
    {
        return User::where('is_active', true)->whereIn('role', ['admin', 'pimpinan'])->get();
    }

    /**
     * nama_role untuk kategori, dipakai form admin saat mengatur pembagian.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            '' => 'Tanpa koordinator (lewat admin)',
            'koordinator_keamanan' => 'Koordinator Keamanan',
            'koordinator_logistik' => 'Koordinator Logistik',
        ];
    }

    /**
     * Tentukan koordinator dari data mentah sebelum Complaint dibuat (dipakai saat store).
     */
    public static function roleForCategoryId(?int $categoryId): ?string
    {
        return self::normalizeRole(Category::whereKey($categoryId)->value('koordinator_role'));
    }

    /** Hanya role koordinator yang dikenal yang boleh dipakai sebagai tujuan. */
    public static function normalizeRole(?string $role): ?string
    {
        return $role !== null && in_array($role, User::KOORDINATOR_ROLES, true) ? $role : null;
    }
}
