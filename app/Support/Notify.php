<?php

namespace App\Support;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Kirim notifikasi database + siaran realtime (Reverb) sekaligus.
 *
 * Catatan: jangan membungkus notifikasi dengan kelas lain yang hanya punya
 * `toArray()` milik pembungkus — channel `database` memakai `toDatabase()` /
 * `toArray()` milik notifikasi ASLI, sedangkan channel `broadcast`
 * memerlukan `broadcastOn()`. Keduanya digabung di notifikasi terkirim.
 */
class Notify
{
    /**
     * Abaikan tujuan kosong: laporan tamu/anonymous tidak punya pelapor akun,
     * sehingga tidak ada yang bisa dikirimi notifikasi.
     */
    public static function send(?object $notifiable, Notification $notification): void
    {
        if ($notifiable === null) {
            return;
        }

        // Kolom uuid `id` diisi dari properti ini; tanpa nilai, insert gagal
        // (senyap) dan pengguna tidak pernah menerima notifikasi.
        if (empty($notification->id)) {
            $notification->id = (string) Str::uuid();
        }

        $notifiable->notify($notification);
    }
}
