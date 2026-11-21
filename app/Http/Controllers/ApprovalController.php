<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\Complaint;
use App\Models\ComplaintAccess;
use App\Models\User;
use App\Notifications\GenericAnnouncement;
use App\Services\ActivityLogger;
use App\Services\ComplaintStatusService;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Daftar persetujuan hanya menampilkan laporan yang boleh dilihat user
        // (laporan sensitif tanpa akses eksplisit tidak boleh ikut bermunculan).
        $allowedSensitiveIds = ComplaintAccess::where('user_id', $user->id)->pluck('complaint_id');

        $approvals = Approval::with(['complaint.category', 'complaint.reporter', 'requester'])
            ->whereHas('complaint', fn ($q) => $q
                ->where('sensitivity', 'biasa')
                ->orWhereIn('id', $allowedSensitiveIds))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->get('status')))
            ->latest()->paginate(10)->withQueryString();

        return view('approvals.index', ['approvals' => $approvals]);
    }

    public function decide(Request $request, Approval $approval, ComplaintStatusService $service): RedirectResponse
    {
        abort_unless($request->user()->can('decide', $approval->complaint), 403, 'Anda tidak berwenang memutuskan persetujuan ini.');

        $validated = $request->validate([
            'decision' => ['required', 'in:disetujui,ditolak'],
            'decision_note' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if ($approval->status !== 'menunggu') {
            return back()->withErrors(['decision' => 'Persetujuan ini sudah diputuskan.']);
        }

        DB::transaction(function () use ($request, $approval, $validated, $service) {
            $approval->update([
                'decided_by' => $request->user()->id,
                'status' => $validated['decision'],
                'decision_note' => $validated['decision_note'],
                'decided_at' => now(),
            ]);

            $complaint = $approval->complaint;

            if ($approval->type === 'penyelesaian' && $validated['decision'] === 'disetujui') {
                $service->change($complaint->fresh(), 'selesai', $request->user(), 'Penyelesaian disetujui pimpinan/admin.');
            } elseif ($approval->type === 'penyelesaian' && $validated['decision'] === 'ditolak') {
                $service->change($complaint->fresh(), 'sedang_ditangani', $request->user(), 'Hasil dikembalikan untuk perbaikan: '.$validated['decision_note']);
            }

            ActivityLogger::log('approval_decided', "Approval #{$approval->id} ({$approval->type}) {$validated['decision']}", Complaint::class, $complaint->id, [
                'approval_id' => $approval->id,
                'decision' => $validated['decision'],
            ], $request);
        });

        Notify::send(
            $approval->complaint->reporter,
            new GenericAnnouncement(
                'Keputusan pemeriksaan',
                "Tiket {$approval->complaint->ticket_number}: pengajuan {$approval->type} {$validated['decision']}.",
                route('complaints.show', $approval->complaint),
                $approval->complaint->ticket_number
            )
        );

        return back()->with('success', 'Keputusan tersimpan.');
    }

    /** Admin: beri akses eksplisit ke laporan sensitif (need-to-know). */
    public function grantSensitiveAccess(Request $request, Complaint $complaint): RedirectResponse
    {
        abort_unless($request->user()->isRole('admin'), 403);
        abort_unless($complaint->isSensitive(), 422, 'Laporan tidak sensitif.');

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        $target = User::findOrFail($validated['user_id']);
        if (! in_array($target->role, ['admin', 'pimpinan'], true)) {
            return back()->withErrors(['user_id' => 'Akses sensitif hanya untuk admin/pimpinan yang ditunjuk.']);
        }
        if ((int) $target->id === (int) $complaint->user_id) {
            return back()->withErrors(['user_id' => 'Pelapor sudah memiliki akses.']);
        }

        $complaint->accesses()->updateOrCreate(
            ['user_id' => $target->id],
            ['granted_by' => $request->user()->id, 'reason' => $validated['reason']]
        );

        // Catat AKSES tanpa menyalin narasi sensitif.
        ActivityLogger::log('sensitive_access_granted', "Akses laporan sensitif {$complaint->ticket_number} diberikan ke {$target->email}", Complaint::class, $complaint->id, [
            'granted_to' => $target->id,
        ], $request);

        return back()->with('success', "Akses diberikan ke {$target->name}.");
    }
}
