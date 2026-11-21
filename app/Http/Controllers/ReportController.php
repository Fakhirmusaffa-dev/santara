<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->isRole('admin', 'pimpinan'), 403);

        $query = $this->scopedBase($request->user(), $request);

        $stats = [
            'total' => (clone $query)->count(),
            'selesai' => (clone $query)->where('status', 'selesai')->count(),
            'rata_feedback' => round((float) \App\Models\Feedback::whereHas('complaint', fn($q) => $this->applyFilters($q, $request))->avg('rating'), 1),
        ];

        $rows = (clone $query)->with(['category', 'location', 'reporter'])
            ->latest()->paginate(12)->withQueryString();

        return view('reports.index', [
            'rows' => $rows,
            'stats' => $stats,
            'categories' => Category::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
            'filters' => $request->only(['search', 'status', 'priority', 'category_id', 'location_id', 'from', 'to']),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()->isRole('admin', 'pimpinan'), 403);

        $rows = $this->scopedBase($request->user(), $request)
            ->with(['category', 'location', 'reporter'])
            ->orderBy('created_at', 'desc')->limit(2000)->get();

        $filename = 'santara-laporan-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tiket', 'Judul', 'Kategori', 'Lokasi', 'Prioritas', 'Status', 'Pelapor', 'Dibuat']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->ticket_number,
                    $r->isSensitive() ? '[TERBATAS]' : $r->title,
                    $r->category?->name,
                    $r->location?->name,
                    $r->priority,
                    $r->status,
                    $r->isSensitive() ? '[TERBATAS]' : $r->reporter?->name,
                    $r->created_at?->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function scopedBase($user, Request $request)
    {
        $query = Complaint::query();

        if ($user->isRole('pimpinan')) {
            $allowed = \App\Models\ComplaintAccess::where('user_id', $user->id)->pluck('complaint_id');
            $query->where(fn($q) => $q->where('sensitivity', 'biasa')->orWhereIn('id', $allowed));
        } else {
            // Admin: non-sensitif penuh.
            $allowed = \App\Models\ComplaintAccess::where('user_id', $user->id)->pluck('complaint_id');
            $query->where(fn($q) => $q->where('sensitivity', 'biasa')->orWhereIn('id', $allowed));
        }

        return $this->applyFilters($query, $request);
    }

    protected function applyFilters($query, Request $request)
    {
        if ($search = trim((string) $request->get('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }
        foreach (['status', 'priority', 'category_id', 'location_id'] as $f) {
            if ($request->filled($f)) {
                $query->where($f, $request->get($f));
            }
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->get('to'));
        }

        return $query;
    }
}
