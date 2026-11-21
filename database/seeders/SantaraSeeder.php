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
            ['name' => 'Budi Petugas', 'email' => 'petugas@santara.test', 'role' => 'petugas', 'position' => 'Petugas Sarpras'],
            ['name' => 'Siti Petugas', 'email' => 'petugas2@santara.test', 'role' => 'petugas', 'position' => 'Petugas Kebersihan'],
            ['name' => 'Ahmad Santri', 'email' => 'pelapor@santara.test', 'role' => 'pelapor', 'position' => 'Santri'],
        ];

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

        $categories = [
            'Kerusakan Fasilitas',
            'Kerusakan Elektronik',
            'Kebersihan',
            'Sanitasi',
            'Kelistrikan',
            'Air dan Plumbing',
            'Perabot',
            'Keamanan Fasilitas',
            'Lainnya',
        ];

        foreach ($categories as $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true]
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
