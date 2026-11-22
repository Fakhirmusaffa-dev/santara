<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    /**
     * Cek akses laporan sensitif: need-to-know.
     * Sensitif hanya untuk: pelapor, koordinator divisi yg ditugaskan, admin/pimpinan yg diberi akses eksplisit.
     */
    public function viewSensitive(User $user, Complaint $complaint): bool
    {
        if (! $complaint->isSensitive()) {
            return true;
        }

        if ($complaint->user_id === $user->id) {
            return true;
        }

        if ($user->isKoordinator() && (int) $complaint->assigned_to === (int) $user->id) {
            return true;
        }

        if ($user->isRole('admin', 'pimpinan')) {
            return $complaint->accesses()->where('user_id', $user->id)->exists()
                || (int) $complaint->assigned_to === (int) $user->id;
        }

        return false;
    }

    public function view(User $user, Complaint $complaint): bool
    {
        if ($complaint->user_id && (int) $complaint->user_id === (int) $user->id) {
            return true;
        }

        // Admin & pimpinan dengan akses eksplisit boleh membaca laporan sensitif
        // (diberikan lewat triase / permohonan akses) — tanpa akses, dilihat sebagai 404.
        if ($complaint->isSensitive() && $this->viewSensitive($user, $complaint)) {
            return true;
        }

        if ($user->isKoordinator()) {
            if ((int) $complaint->assigned_to === (int) $user->id) {
                return true;
            }
            if ($complaint->isSensitive()) {
                return false;
            }

            // Koordinator hanya melihat laporan yang ditugaskan kepadanya/divisinya
            // (tidak bisa mengintip laporan divisi lain).
            return $complaint->assignments()->where('assigned_to', $user->id)->exists();
        }

        if ($user->isRole('admin')) {
            if ($complaint->isSensitive()) {
                return $this->viewSensitive($user, $complaint);
            }

            return true;
        }

        if ($user->isRole('pimpinan')) {
            // Pimpinan: ringkasan + akses eksplisit untuk sensitif.
            if ($complaint->isSensitive()) {
                return $this->viewSensitive($user, $complaint);
            }

            return true;
        }

        return false;
    }

    public function update(User $user, Complaint $complaint): bool
    {
        // Pelapor (akun) hanya bisa edit miliknya saat masih draft/menunggu_verifikasi.
        if ($complaint->user_id && (int) $complaint->user_id === (int) $user->id
            && in_array($complaint->status, ['draft', 'menunggu_verifikasi'], true)) {
            return true;
        }

        // Laporan sensitif: admin/pimpinan wajib punya akses eksplisit dulu.
        if ($complaint->isSensitive() && ! $this->viewSensitive($user, $complaint)) {
            return false;
        }

        return $user->isRole('admin');
    }

    /** Hapus laporan: admin bebas; pelapor hanya miliknya yang masih menunggu_verifikasi. */
    public function delete(User $user, Complaint $complaint): bool
    {
        // Laporan sensitif yang dihapus wajib tercatat siapa yang menghapus
        // — tetap lewat jalur akses eksplisit, bukan jabatan semata.
        if ($complaint->isSensitive() && ! $this->viewSensitive($user, $complaint)) {
            return false;
        }

        if ($user->isRole('admin')) {
            return true;
        }

        return (bool) $complaint->user_id
            && (int) $complaint->user_id === (int) $user->id
            && $complaint->status === 'menunggu_verifikasi';
    }

    public function verify(User $user, Complaint $complaint): bool
    {
        if ($complaint->isSensitive() && ! $this->viewSensitive($user, $complaint)) {
            return false;
        }

        return $user->isRole('admin') && $complaint->status === 'menunggu_verifikasi'
            && $this->noConflict($user, $complaint);
    }

    public function assign(User $user, Complaint $complaint): bool
    {
        if ($complaint->isSensitive() && ! $this->viewSensitive($user, $complaint)) {
            return false;
        }

        return $user->isRole('admin')
            && in_array($complaint->status, ['terverifikasi', 'menunggu_penugasan'], true)
            && $this->noConflict($user, $complaint);
    }

    public function handle(User $user, Complaint $complaint): bool
    {
        if ($complaint->isSensitive() && ! $this->viewSensitive($user, $complaint)) {
            return false;
        }

        return $user->isKoordinator()
            && (int) $complaint->assigned_to === (int) $user->id
            && $complaint->isOpen();
    }

    public function decide(User $user, Complaint $complaint): bool
    {
        if ($complaint->isSensitive() && ! $this->viewSensitive($user, $complaint)) {
            return false;
        }

        return $user->isRole('pimpinan', 'admin') && $this->noConflict($user, $complaint);
    }

    /** Konflik kepentingan: pihak yang dilaporkan / terlibat tidak boleh menangani. */
    protected function noConflict(User $user, Complaint $complaint): bool
    {
        // Aturan sederhana namun nyata: pelapor tidak boleh memverifikasi/menugaskan laporannya sendiri
        // kecuali ia admin yang memang bertugas — dan admin yang menjadi pelapor tidak boleh memprosesnya.
        if ((int) $complaint->user_id === (int) $user->id) {
            return false;
        }

        return true;
    }
}
