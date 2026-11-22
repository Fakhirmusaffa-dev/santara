<?php

namespace App\Services;

use App\Models\Complaint;
use Illuminate\Support\Str;

/**
 * Menentukan klasifikasi & prioritas sebuah laporan TANPA bertanya ke pelapor.
 *
 * Aturan (satu arah, tidak bisa ditimpa input):
 *   1. Kategori yang ditandai "wajib ditangani khusus" (mis. Keamanan) -> sensitif.
 *   2. Judul/deskripsi memuat kata kunci berisiko (pembulian, perundungan, dll)
 *      -> sensitif, walaupun kategorinya umum.
 *   3. Selain itu -> biasa.
 *
 * Prioritas selalu diturunkan dari klasifikasi lewat Complaint::PRIORITY_BY_SENSITIVITY.
 */
class ComplaintClassifier
{
    /**
     * Kata kunci yang menandakan laporan perlu penanganan khusus.
     * Ditulis dalam bentuk dasar (tanpa imbuhan) karena pencocokan dilakukan
     * pada teks yang sudah dinormalisasi; imbuhan seperti "pem-" / "-an"
     * tetap cocok karena kata dasarnya utuh di dalamnya.
     *
     * @var list<string>
     */
    public const SENSITIVE_KEYWORDS = [
        // perundungan / kekerasan
        'bully',
        'bullying',
        'pembully',
        'rundung',
        'perundungan',
        'hina',
        'hinaan',
        'ejek',
        'ejekan',
        'olok',
        'olokan',
        'caci',
        'cacian',
        'ancam',
        'ancaman',
        'mengancam',
        'intimidasi',
        'pemerasan',
        'peras',
        'pukul',
        'pemukulan',
        'dipukul',
        'memukul',
        'tampar',
        'ditampar',
        'tendang',
        'ditendang',
        'kekerasan',
        'siksa',
        'penyiksaan',
        'keroyok',
        'dikeroyok',
        'kroyok',
        // kesejahteraan santri
        'leceh',
        'pelecehan',
        'cabul',
        'pencabulan',
        'asusila',
        'perkosa',
        'pemerkosaan',
        'pelecehan seksual',
        'seksual',
        'narkoba',
        'narkotika',
        'miras',
        'minuman keras',
        'mabuk',
        // keselamatan
        'bunuh diri',
        'mengancam bunuh diri',
        'depresi',
        'putus asa',
        'senjata',
        'sajam',
        'celurit',
        'pisau',
        'begal',
        'rampok',
        'kebakaran',
        'terbakar',
        'korsleting',
        'ambruk',
        'runtuh',
        'kecelakaan',
        'luka berat',
        'pingsan',
        'darurat',
        // privasi
        'rahasia',
        'privasi',
        'data pribadi',
        'doxing',
        'sebar foto',
    ];

    /**
     * Klasifikasi akhir untuk sebuah laporan.
     *
     * @return array{sensitivity: string, priority: string, reason: string}
     */
    public function classify(?string $categorySensitivity, ?string $title, ?string $description = null): array
    {
        $sensitivity = $categorySensitivity === 'sensitif' ? 'sensitif' : 'biasa';
        $reason = $sensitivity === 'sensitif' ? 'category' : 'none';

        if ($sensitivity !== 'sensitif' && $this->containsRiskKeyword($title, $description)) {
            $sensitivity = 'sensitif';
            $reason = 'keyword';
        }

        return [
            'sensitivity' => $sensitivity,
            'priority' => Complaint::priorityForSensitivity($sensitivity),
            'reason' => $reason,
        ];
    }

    /**
     * Apakah judul/deskripsi memuat kata kunci berisiko.
     */
    public function containsRiskKeyword(?string $title, ?string $description = null): bool
    {
        // Diperiksa terpisah: judul dan isi utuh. Penggabungan mentah bisa memotong
        // kata di sambungan judul->deskripsi, jadi normalisasi dilakukan per bagian.
        return $this->matches($title) || $this->matches($description);
    }

    private function matches(?string $text): bool
    {
        if ($text === null || trim($text) === '') {
            return false;
        }

        // Huruf kecil, dan tanda baca diubah jadi spasi agar "bully!" / "bully,"
        // tetap cocok tanpa harus mendaftar setiap variasi.
        $haystack = ' '.preg_replace('/[^\p{L}\p{N}]+/u', ' ', Str::lower($text)).' ';

        foreach (self::SENSITIVE_KEYWORDS as $keyword) {
            $needle = Str::lower($keyword);

            if (str_contains($needle, ' ')) {
                if (str_contains($haystack, ' '.$needle.' ')) {
                    return true;
                }

                continue;
            }

            // Kata tunggal: cocokkan sebagai awalan kata, agar "bully",
            // "bullyan", "bullying" semuanya tertangkap tanpa salah cocok
            // dengan kata lain yang hanya mengandung potongan huruf itu.
            if (preg_match('/(?<![\p{L}])'.preg_quote($needle, '/').'/u', $haystack) === 1) {
                return true;
            }
        }

        return false;
    }
}
