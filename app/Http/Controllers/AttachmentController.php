<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintPhoto;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * File privat: hanya yang berhak view complaint + Gate sensitif.
     */
    public function show(ComplaintPhoto $photo): StreamedResponse
    {
        $complaint = $photo->complaint;

        $this->ensureCanView($complaint);

        abort_unless(Storage::disk('local')->exists($photo->path), 404);

        $mime = $photo->mime_type ?: Storage::disk('local')->mimeType($photo->path);

        return Storage::disk('local')->response($photo->path, $photo->original_name, [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function handling(string $path): StreamedResponse
    {
        // Path terikat ke complaint: complaints/{id}/handling/...
        // Normalisasi ke string murni (trim, tanpa segmen kosong).
        $segments = array_values(array_filter(
            explode('/', trim($path, '/')),
            fn (string $segment): bool => $segment !== ''
        ));
        abort_unless(
            count($segments) === 4
            && $segments[0] === 'complaints'
            && ctype_digit($segments[1])
            && $segments[2] === 'handling',
            404
        );

        $complaintId = (int) $segments[1];
        $complaint = Complaint::findOrFail($complaintId);
        $this->ensureCanView($complaint);

        // Bangun ulang path kanonik dari segmen yang sudah dinormalisasi.
        $full = 'complaints/'.$complaintId.'/handling/'.$segments[3];

        // Hanya file yang memang tercatat sebagai bukti penanganan laporan ini.
        // Tanpa ini, path tebakan (mis. foto laporan lain) bisa diunduh mentah.
        $log = $complaint->handlingLogs()->where('photo_path', $full)->first();
        abort_unless($log && Storage::disk('local')->exists($log->photo_path), 404);

        return Storage::disk('local')->response($log->photo_path, null, ['X-Content-Type-Options' => 'nosniff']);
    }

    /**
     * Tolak akses tanpa membocorkan keberadaan berkas (403, bukan 404 yang
     * mengisyaratkan ada sesuatu).
     */
    private function ensureCanView(Complaint $complaint): void
    {
        $user = request()->user();

        abort_unless($user && $user->can('view', $complaint), 403, 'Anda tidak berwenang melihat lampiran ini.');

        if ($complaint->isSensitive()) {
            abort_unless($user->can('viewSensitive', $complaint), 403, 'Anda tidak berwenang melihat lampiran sensitif ini.');
        }
    }
}
