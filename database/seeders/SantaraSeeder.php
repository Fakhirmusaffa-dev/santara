<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SantaraSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Administrator', 'email' => 'admin@santara.test', 'role' => 'admin', 'position' => 'Admin SIT'],
            ['name' => 'Kepala Sekolah', 'email' => 'pimpinan@santara.test', 'role' => 'pimpinan', 'position' => 'Kepala Sekolah'],
            ['name' => 'Koordinator Keamanan', 'email' => 'keamanan@santara.test', 'role' => 'koordinator_keamanan', 'position' => 'Koordinator Keamanan'],
            ['name' => 'Koordinator Logistik', 'email' => 'logistik@santara.test', 'role' => 'koordinator_logistik', 'position' => 'Koordinator Logistik'],
            ['name' => 'Ahmad Santri', 'email' => 'pelapor@santara.test', 'role' => 'pelapor', 'position' => 'Santri'],
            // Akun penangan umum (sebelumnya role `petugas`) dulu ikut menerima laporan
            // logistik. Sekarang hanya koordinator yang ditunjuk sistem (`assigned_to`)
            // yang memegang alur penanganan, jadi akun ini tidak didaftarkan lagi.
            // Tetap divalidasi policy bila nantinya dibuat ulang sebagai koordinator.
        ];

        // Akun lama tanpa pemakaian data apa pun boleh dibersihkan (idempoten saat seed ulang).
        User::where('email', 'petugas@santara.test')
            ->whereDoesntHave('complaints')
            ->whereDoesntHave('assignedComplaints')
            ->whereDoesntHave('assignments')
            ->whereDoesntHave('handlingLogs')
            ->delete();

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'role' => $data['role'],
                    'position' => $data['position'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        // Peta kategori -> divisi koordinator penerima laporan.
        //   sarana & prasarana -> Koordinator Logistik
        //   keamanan           -> Koordinator Keamanan
        // Kategori "Kerusakan Fasilitas" = sarana & prasarana (tidak sensitif) sekaligus
        // kategori umum bawaan aplikasi.
        $categories = [
            'Kerusakan Fasilitas' => 'koordinator_logistik',
            'Sarana dan Prasarana' => 'koordinator_logistik',
            'Kerusakan Elektronik' => 'koordinator_logistik',
            'Kebersihan' => 'koordinator_logistik',
            'Sanitasi' => 'koordinator_logistik',
            'Kelistrikan' => 'koordinator_logistik',
            'Air dan Plumbing' => 'koordinator_logistik',
            'Perabot' => 'koordinator_logistik',
            'Keamanan' => 'koordinator_keamanan',
            'Keamanan Fasilitas' => 'koordinator_keamanan',
            'Lainnya' => null,
        ];

        foreach ($categories as $name => $koordinatorRole) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'is_active' => true,
                    'koordinator_role' => $koordinatorRole,
                    // Kategori keamanan = perlu penanganan khusus.
                    'is_sensitive' => $koordinatorRole === 'koordinator_keamanan',
                ]
            );
        }

        $locations = [
            ['name' => 'Asrama Putra Blok A', 'code' => 'ASP-A', 'area' => 'putra'],
            ['name' => 'Asrama Putra Blok B', 'code' => 'ASP-B', 'area' => 'putra'],
            ['name' => 'Kamar Mandi Putra', 'code' => 'KM-PA', 'area' => 'putra'],
            ['name' => 'Asrama Putri Blok A', 'code' => 'ASW-A', 'area' => 'putri'],
            ['name' => 'Asrama Putri Blok B', 'code' => 'ASW-B', 'area' => 'putri'],
            ['name' => 'Kamar Mandi Putri', 'code' => 'KM-PI', 'area' => 'putri'],
            ['name' => 'Masjid Utama', 'code' => 'MSJ-01', 'area' => 'umum'],
            ['name' => 'Ruang Kelas 1', 'code' => 'RK-01', 'area' => 'umum'],
            ['name' => 'Ruang Kelas 2', 'code' => 'RK-02', 'area' => 'umum'],
            ['name' => 'Kantin', 'code' => 'KNT-01', 'area' => 'umum'],
            ['name' => 'Laboratorium Komputer', 'code' => 'LAB-01', 'area' => 'umum'],
            ['name' => 'Perpustakaan', 'code' => 'PER-01', 'area' => 'umum'],
            ['name' => 'Lapangan', 'code' => 'LPG-01', 'area' => 'umum'],
            ['name' => 'Kantor Administrasi', 'code' => 'KTR-01', 'area' => 'umum'],
        ];

        foreach ($locations as $loc) {
            Location::updateOrCreate(
                ['name' => $loc['name']],
                ['code' => $loc['code'], 'area' => $loc['area'], 'is_active' => true]
            );
        }
    }
}
