<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintAccess;
use App\Models\Feedback;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function pelapor(): View
    {
        $user = auth()->user();

        $complaints = $user->complaints()->with(['category', 'location'])->latest();

        return view('dashboards.pelapor', [
            'total' => (clone $complaints)->count(),
            'diproses' => (clone $complaints)->whereNotIn('status', ['selesai', 'ditolak', 'ditutup'])->count(),
            'selesai' => (clone $complaints)->where('status', 'selesai')->count(),
            'terbaru' => $complaints->take(6)->get(),
            'menunggu' => (clone $complaints)->where('status', 'menunggu_verifikasi')->count(),
            'ditangani' => (clone $complaints)->whereIn('status', ['ditugaskan', 'sedang_ditangani', 'terverifikasi', 'menunggu_pemeriksaan'])->count(),
            'notifikasi' => $user->notifications()->latest()->take(5)->get(),
        ]);
    }

    /** Dashboard koordinator (keamanan / logistik): laporan divisinya sendiri. */
    public function koordinator(): View
    {
        $user = auth()->user();

        $base = $user->assignedComplaints();

        return view('dashboards.koordinator', [
            'total' => $user->assignments()->count(),
            'baru' => $user->assignments()->where('status', 'baru')->count(),
            'ditangani' => (clone $base)->whereIn('status', ['ditugaskan', 'sedang_ditangani'])->count(),
            'pemeriksaan' => (clone $base)->where('status', 'menunggu_pemeriksaan')->count(),
            'mendesak' => (clone $base)->whereIn('priority', ['tinggi', 'mendesak'])
                ->whereNotIn('status', ['selesai', 'ditolak', 'ditutup'])->count(),
            'tugas' => (clone $base)->with(['category', 'location', 'reporter'])->latest()->take(8)->get(),
        ]);
    }

    public function admin(): View
    {
        $base = Complaint::query()->where('sensitivity', 'biasa');

        $byStatus = (clone $base)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        // Agregasi bulan di PHP agar jalan di MySQL maupun SQLite (test).
        $bulanan = (clone $base)->orderBy('created_at', 'desc')->take(200)->get()
            ->groupBy(fn ($c) => $c->created_at->format('Y-m'))
            ->map(fn ($rows, $bulan) => (object) ['bulan' => $bulan, 'total' => $rows->count()])
            ->sortKeysDesc()->take(6)->values();

        return view('dashboards.admin', [
            'total' => (clone $base)->count(),
            'byStatus' => $byStatus,
            'byKategori' => Category::withCount(['complaints' => fn ($q) => $q->where('sensitivity', 'biasa')])->orderByDesc('complaints_count')->take(6)->get(),
            'terbaru' => Complaint::with(['category', 'location', 'reporter'])->where('sensitivity', 'biasa')->latest()->take(8)->get(),
            'bulanan' => $bulanan,
            'rataFeedback' => round((float) Feedback::avg('rating'), 1),
        ]);
    }

    public function pimpinan(): View
    {
        // Pimpinan: hanya agregat non-sensitif + akses eksplisit sensitif.
        $user = auth()->user();
        $allowedSensitiveIds = $user->isRole('pimpinan')
            ? ComplaintAccess::where('user_id', $user->id)->pluck('complaint_id')
            : collect();

        $base = Complaint::query()
            ->where(fn ($q) => $q->where('sensitivity', 'biasa')->orWhereIn('id', $allowedSensitiveIds));

        $byStatus = (clone $base)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('dashboards.pimpinan', [
            'total' => (clone $base)->count(),
            'byStatus' => $byStatus,
            'prioritasTinggi' => (clone $base)->whereIn('priority', ['tinggi', 'mendesak'])->whereNotIn('status', ['selesai', 'ditolak', 'ditutup'])->count(),
            'menungguPersetujuan' => Approval::where('status', 'menunggu')->count(),
            'penyelesaian' => ((int) ($byStatus['selesai'] ?? 0)) > 0 && (clone $base)->count() > 0
                ? round(((int) ($byStatus['selesai'] ?? 0)) / (clone $base)->count() * 100, 1)
                : 0,
            'distribusiKategori' => Category::withCount(['complaints' => function ($q) use ($allowedSensitiveIds) {
                $q->where(fn ($qq) => $qq->where('sensitivity', 'biasa')->orWhereIn('id', $allowedSensitiveIds));
            }])->orderByDesc('complaints_count')->take(6)->get(),
            'butuhPerhatian' => (clone $base)->with(['category', 'reporter'])->whereIn('priority', ['tinggi', 'mendesak'])->latest()->take(6)->get(),
        ]);
    }
}
