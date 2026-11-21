<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\View\View;

/**
 * Triase laporan sensitif (need-to-know).
 *
 * Prinsip: halaman ini HANYA menampilkan metadata (tiket, prioritas,
 * status, waktu masuk) — tidak pernah judul, isi, nama pelapor, foto,
 * kategori, maupun lokasi. Admin menunjuk penangan dari sini lewat
 * pemberian akses tercatat, baru setelah itu isi boleh dibuka.
 */
class SensitiveTriageController extends Controller
{
    public function index(): View
    {
        $me = auth()->id();

        $antrean = Complaint::query()
            ->where('sensitivity', 'sensitif')
            ->select(['id', 'ticket_number', 'priority', 'status', 'user_id', 'assigned_to', 'created_at'])
            ->withCount([
                'accesses',
                'accesses as milik_saya' => fn($q) => $q->where('user_id', $me),
            ])
            ->orderByRaw("CASE priority WHEN 'mendesak' THEN 0 WHEN 'tinggi' THEN 1 WHEN 'sedang' THEN 2 ELSE 3 END")
            ->latest()
            ->paginate(12);

        $handlers = User::whereIn('role', ['admin', 'pimpinan'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('admin.sensitive.index', [
            'antrean' => $antrean,
            'handlers' => $handlers,
            'total' => Complaint::where('sensitivity', 'sensitif')->count(),
            'tanpaPenangan' => Complaint::where('sensitivity', 'sensitif')
                ->whereDoesntHave('accesses')->whereNull('assigned_to')->count(),
            'milikSaya' => Complaint::where('sensitivity', 'sensitif')
                ->whereHas('accesses', fn($q) => $q->where('user_id', $me))->count(),
        ]);
    }
}
