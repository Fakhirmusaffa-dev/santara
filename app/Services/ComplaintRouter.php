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
 * Laporan sensitif (mis. pembulian) TETAP dirutekan ke koordinator keamanan,
 * tetapi akses isinya tetap dikunci policy need-to-know; admin hanya diberi
 * tembusan metadata untuk pengawasan, bukan isi laporan.
 */
class ComplaintRouter
{
    /**
     * Koordinator penerima laporan ini.
     *
     * @return Collection<int, User>
     */
    public function recipientsFor(Complaint $complaint): Collection
    {
        $role = $this->roleFor($complaint);

        if ($role === null) {
            return collect();
        }

        return User::koordinatorFor($role);
    }

    /** Role koordinator tujuan berdasarkan kategori laporan. */
    public function roleFor(Complaint $complaint): ?string
    {
        $role = $complaint->category?->koordinator_role;

        if ($role === null) {
            return null;
        }

        return in_array($role, User::KOORDINATOR_ROLES, true) ? $role : null;
    }

    /** Apakah laporan ini punya koordinator tujuan (bila tidak, jatuh ke admin). */
    public function isRoutable(Complaint $complaint): bool
    {
        return $this->recipientsFor($complaint)->isNotEmpty();
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
        $role = Category::whereKey($categoryId)->value('koordinator_role');

        return $role !== null && in_array($role, User::KOORDINATOR_ROLES, true) ? $role : null;
    }
}
