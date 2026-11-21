<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComplaintRequest;
use App\Http\Requests\UpdateComplaintRequest;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintAccess;
use App\Models\ComplaintStatusHistory;
use App\Models\Location;
use App\Models\User;
use App\Notifications\GenericAnnouncement;
use App\Services\ActivityLogger;
use App\Services\ComplaintStatusService;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = $this->scopedQuery($user);

        if ($search = trim((string) $request->get('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }
        foreach (['status', 'priority', 'category_id', 'location_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->get($filter));
            }
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->get('to'));
        }

        $complaints = $query->with(['category', 'location', 'reporter', 'assignee'])
            ->latest()->paginate(10)->withQueryString();

        return view('complaints.index', [
            'complaints' => $complaints,
            'categories' => Category::active()->orderBy('name')->get(),
            'locations' => Location::active()->orderBy('name')->get(),
            'filters' => $request->only(['search', 'status', 'priority', 'category_id', 'location_id', 'from', 'to']),
        ]);
    }

    public function create(): View
    {
        return view('complaints.create', [
            'categories' => Category::active()->orderBy('name')->get(),
            'locations' => Location::active()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreComplaintRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $complaint = DB::transaction(function () use ($request, $data) {
            $complaint = Complaint::create([
                'ticket_number' => $this->generateTicketNumber(),
                'user_id' => $request->user()->id,
                'category_id' => $data['category_id'],
                'location_id' => $data['location_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'priority' => $data['priority'],
                'sensitivity' => $data['sensitivity'],
                'status' => 'menunggu_verifikasi',
            ]);

            $this->storePhotos($request, $complaint, 'laporan');

            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status' => null,
                'to_status' => 'menunggu_verifikasi',
                'changed_by' => $request->user()->id,
                'note' => 'Laporan dibuat pelapor.',
            ]);

            ActivityLogger::log('complaint_created', "Tiket {$complaint->ticket_number} dibuat", Complaint::class, $complaint->id, [
                'ticket' => $complaint->ticket_number,
            ], $request);

            return $complaint;
        });

        Notify::send(
            $request->user(),
            new GenericAnnouncement(
                'Laporan terkirim',
                "Laporan {$complaint->ticket_number} berhasil dikirim dan menunggu verifikasi.",
                route('complaints.show', $complaint),
                $complaint->ticket_number
            )
        );

        // Triase buta: beri tahu semua admin aktif bahwa ada tiket sensitif
        // yang butuh penunjukan penangan — TANPA menyalin isi laporan.
        if ($complaint->sensitivity === 'sensitif') {
            $admins = User::where('role', 'admin')
                ->where('is_active', true)
                ->whereKeyNot($request->user()->id)
                ->get();
            foreach ($admins as $admin) {
                Notify::send(
                    $admin,
                    new GenericAnnouncement(
                        'Laporan sensitif masuk',
                        "Tiket {$complaint->ticket_number} membutuhkan penunjukan penangan di Sensitif Masuk.",
                        route('admin.sensitive.index'),
                        $complaint->ticket_number
                    )
                );
            }
        }

        return redirect()->route('complaints.show', $complaint)
            ->with('success', "Laporan {$complaint->ticket_number} berhasil dikirim.");
    }

    public function show(Complaint $complaint): View
    {
        // Penyamaran sumber daya: yang tidak berhak diberi 404, bukan petunjuk bahwa tiket ini ada.
        abort_unless(auth()->user()->can('view', $complaint), 404);

        $complaint->load([
            'category',
            'location',
            'reporter',
            'assignee',
            'verifier',
            'photos.uploader',
            'statusHistories.changer',
            'assignments.assignee',
            'handlingLogs.user',
            'approvals.requester',
            'feedback.user',
        ]);

        $canViewSensitive = auth()->user()->can('viewSensitive', $complaint);

        return view('complaints.show', compact('complaint', 'canViewSensitive'));
    }

    public function edit(Complaint $complaint): View
    {
        abort_unless(auth()->user()->can('update', $complaint), 404);

        return view('complaints.edit', [
            'complaint' => $complaint,
            'categories' => Category::active()->orderBy('name')->get(),
            'locations' => Location::active()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateComplaintRequest $request, Complaint $complaint): RedirectResponse
    {
        abort_unless($request->user()->can('update', $complaint), 404);

        $data = $request->validated();

        DB::transaction(function () use ($request, $complaint, $data) {
            $complaint->update([
                'category_id' => $data['category_id'],
                'location_id' => $data['location_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'priority' => $data['priority'],
            ]);

            $this->storePhotos($request, $complaint, 'laporan');

            // Pelapor boleh mengklasifikasi ulang laporannya sendiri, asalkan belum ditangani
            // (masih draft/menunggu verifikasi) — mencegah laporan sensitif “dibuka”.
            $isReporterOwn = $complaint->user_id && (int) $complaint->user_id === (int) $request->user()->id;
            $canReclassify = $isReporterOwn
                && in_array($complaint->status, ['draft', 'menunggu_verifikasi'], true);

            if (($isReporterOwn || $request->user()->isRole('admin')) && isset($data['sensitivity']) && $canReclassify) {
                $complaint->update(['sensitivity' => $data['sensitivity']]);
            }

            ActivityLogger::log('complaint_updated', "Tiket {$complaint->ticket_number} diperbarui", Complaint::class, $complaint->id, [], $request);
        });

        return redirect()->route('complaints.show', $complaint)->with('success', 'Laporan diperbarui.');
    }

    public function destroy(Request $request, Complaint $complaint): RedirectResponse
    {
        // Hapus hanya boleh bila hapus (policy) DAN lihat (bukan penyamaran) diizinkan.
        abort_unless(
            $request->user()->can('delete', $complaint) && $request->user()->can('view', $complaint),
            403,
            'Anda tidak berwenang menghapus laporan ini.'
        );

        $ticket = $complaint->ticket_number;

        DB::transaction(function () use ($complaint) {
            // Bersihkan notifikasi bertiket ini agar tidak jadi tautan mati.
            User::whereNotNull('id')->chunkById(100, function ($users) use ($complaint) {
                foreach ($users as $user) {
                    $user->notifications()
                        ->where('data', 'like', '%' . $complaint->ticket_number . '%')
                        ->delete();
                }
            });

            $complaint->delete();
        });

        ActivityLogger::log('complaint_deleted', "Tiket {$ticket} dihapus", null, null, [], $request);

        return redirect()->route('complaints.index')->with('success', "Laporan {$ticket} dihapus.");
    }

    /** Scope daftar sesuai role + klasifikasi sensitif. */
    protected function scopedQuery($user)
    {
        $query = Complaint::query();

        if ($user->isRole('petugas')) {
            return $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhereHas('assignments', fn($a) => $a->where('assigned_to', $user->id));
            });
        }

        if ($user->isRole('pimpinan')) {
            $allowed = ComplaintAccess::where('user_id', $user->id)->pluck('complaint_id');

            return $query->where(fn($q) => $q->where('sensitivity', 'biasa')->orWhereIn('id', $allowed));
        }

        // Admin: non-sensitif penuh + sensitif yang diberi akses eksplisit / ditugaskan ke dirinya.
        $allowed = ComplaintAccess::where('user_id', $user->id)->pluck('complaint_id');

        return $query->where(fn($q) => $q->where('sensitivity', 'biasa')
            ->orWhereIn('id', $allowed)
            ->orWhere('assigned_to', $user->id));
    }

    protected function generateTicketNumber(): string
    {
        do {
            $ticket = 'STR-' . now()->format('Ymd') . '-' . strtoupper(bin2hex(random_bytes(6)));
        } while (Complaint::where('ticket_number', $ticket)->exists());

        return $ticket;
    }

    protected function storePhotos(Request $request, Complaint $complaint, string $kind): void
    {
        if (! $request->hasFile('photos')) {
            return;
        }

        foreach ((array) $request->file('photos') as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $path = $file->store('complaints/' . $complaint->id, 'local');
            $complaint->photos()->create([
                'uploaded_by' => $request->user()->id,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'kind' => $kind,
            ]);
        }
    }

    /** Admin: verifikasi / tolak. */
    public function verify(Request $request, Complaint $complaint, ComplaintStatusService $service): RedirectResponse
    {
        abort_unless($request->user()->can('verify', $complaint), 403, 'Anda tidak berwenang memverifikasi laporan ini.');

        $validated = $request->validate([
            'verification_note' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($complaint, $request, $validated, $service) {
            $complaint->update([
                'verified_by' => $request->user()->id,
                'verification_note' => $validated['verification_note'] ?? null,
                'verified_at' => now(),
            ]);
            $service->change($complaint->fresh(), 'terverifikasi', $request->user(), 'Laporan terverifikasi.');
            ActivityLogger::log('complaint_verified', "Tiket {$complaint->ticket_number} diverifikasi", Complaint::class, $complaint->id, [], $request);
        });

        Notify::send(
            $complaint->reporter,
            new GenericAnnouncement(
                'Laporan terverifikasi',
                "Tiket {$complaint->ticket_number} telah terverifikasi.",
                route('complaints.show', $complaint),
                $complaint->ticket_number
            )
        );

        return back()->with('success', 'Laporan terverifikasi.');
    }

    public function reject(Request $request, Complaint $complaint, ComplaintStatusService $service): RedirectResponse
    {
        abort_unless($request->user()->can('verify', $complaint), 403, 'Anda tidak berwenang menolak laporan ini.');

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        DB::transaction(function () use ($complaint, $request, $validated, $service) {
            $complaint->update([
                'rejection_reason' => $validated['rejection_reason'],
                'rejected_at' => now(),
            ]);
            $service->change($complaint->fresh(), 'ditolak', $request->user(), $validated['rejection_reason']);
            ActivityLogger::log('complaint_rejected', "Tiket {$complaint->ticket_number} ditolak", Complaint::class, $complaint->id, [], $request);
        });

        Notify::send(
            $complaint->reporter,
            new GenericAnnouncement(
                'Laporan ditolak',
                "Tiket {$complaint->ticket_number} ditolak. Lihat alasan pada detail laporan.",
                route('complaints.show', $complaint),
                $complaint->ticket_number
            )
        );

        return back()->with('success', 'Laporan ditolak dengan alasan tercatat.');
    }

    /** Admin: tugaskan petugas. */
    public function assign(Request $request, Complaint $complaint): RedirectResponse
    {
        abort_unless($request->user()->can('assign', $complaint), 403, 'Anda tidak berwenang menugaskan laporan ini.');

        $validated = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
            'instruction' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $petugas = User::whereKey($validated['assigned_to'])
            ->where('role', 'petugas')->where('is_active', true)->firstOrFail();

        // Konflik kepentingan: petugas tidak boleh menangani laporannya sendiri.
        if ((int) $petugas->id === (int) $complaint->user_id) {
            return back()->withErrors(['assigned_to' => 'Petugas tidak boleh menangani laporannya sendiri (konflik kepentingan).']);
        }

        DB::transaction(function () use ($request, $complaint, $petugas, $validated) {
            $complaint->assignments()->create([
                'assigned_to' => $petugas->id,
                'assigned_by' => $request->user()->id,
                'instruction' => $validated['instruction'],
                'status' => 'baru',
            ]);

            $complaint->update(['assigned_to' => $petugas->id]);

            if ($complaint->status === 'terverifikasi') {
                app(ComplaintStatusService::class)->change($complaint->fresh(), 'menunggu_penugasan', $request->user(), 'Menunggu penugasan.');
                $complaint = $complaint->fresh();
            }
            app(ComplaintStatusService::class)->change($complaint, 'ditugaskan', $request->user(), "Ditugaskan ke {$petugas->name}.");

            ActivityLogger::log('complaint_assigned', "Tiket {$complaint->ticket_number} ditugaskan ke {$petugas->name}", Complaint::class, $complaint->id, [
                'assigned_to' => $petugas->id,
            ], $request);
        });

        Notify::send(
            $petugas,
            new GenericAnnouncement(
                'Penugasan baru',
                "Anda ditugaskan menangani tiket {$complaint->ticket_number}.",
                route('complaints.show', $complaint),
                $complaint->ticket_number
            )
        );

        return back()->with('success', "Laporan ditugaskan ke {$petugas->name}.");
    }

    /** Admin: ubah status terkontrol. Petugas/pimpinan memakai alur terkontrol masing-masing. */
    public function changeStatus(Request $request, Complaint $complaint, ComplaintStatusService $service): RedirectResponse
    {
        // Hanya admin lewat jalur ini; peran lain memakai alur terkontrol yang ber-policy.
        abort_unless($request->user()->isRole('admin'), 403, 'Anda tidak berwenang mengubah status laporan ini.');

        $validated = $request->validate([
            'to_status' => ['required', 'string', Rule::in(Complaint::STATUSES)],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();

        // Admin tetap wajib lolos policy (termasuk batas akses laporan sensitif & konflik kepentingan).
        if (! $user->can('decide', $complaint)) {
            abort(403, 'Anda tidak berwenang mengubah status laporan ini.');
        }

        try {
            $service->change($complaint, $validated['to_status'], $user, $validated['note'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['to_status' => $e->getMessage()]);
        }

        ActivityLogger::log('status_changed_manual', "Tiket {$complaint->ticket_number} -> {$validated['to_status']}", Complaint::class, $complaint->id, [], $request);

        return back()->with('success', 'Status laporan diperbarui.');
    }

    /** Petugas: catat progres + dokumentasi. */
    public function addHandlingLog(Request $request, Complaint $complaint): RedirectResponse
    {
        abort_unless($request->user()->can('handle', $complaint), 403, 'Anda tidak berwenang menangani laporan ini.');

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:180'],
            'note' => ['required', 'string', 'min:5', 'max:2000'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        DB::transaction(function () use ($request, $complaint, $validated) {
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('complaints/' . $complaint->id . '/handling', 'local');
            }

            $complaint->handlingLogs()->create([
                'user_id' => $request->user()->id,
                'title' => $validated['title'] ?? null,
                'note' => $validated['note'],
                'progress' => $validated['progress'] ?? null,
                'photo_path' => $photoPath,
            ]);

            if ($complaint->status === 'ditugaskan') {
                app(ComplaintStatusService::class)->change($complaint->fresh(), 'sedang_ditangani', $request->user(), 'Penanganan dimulai.');
            }

            ActivityLogger::log('handling_logged', "Progres tiket {$complaint->ticket_number} dicatat", Complaint::class, $complaint->id, [], $request);
        });

        return back()->with('success', 'Progres penanganan dicatat.');
    }

    /** Petugas: terima tugas. */
    public function acceptAssignment(Request $request, Complaint $complaint): RedirectResponse
    {
        abort_unless($request->user()->can('handle', $complaint), 403, 'Anda tidak berwenang menangani laporan ini.');

        $assignment = $complaint->assignments()->where('assigned_to', $request->user()->id)->latest()->firstOrFail();
        $assignment->update(['status' => 'diterima', 'accepted_at' => now()]);

        ActivityLogger::log('assignment_accepted', "Tiket {$complaint->ticket_number} diterima petugas", Complaint::class, $complaint->id, [], $request);

        return back()->with('success', 'Penugasan diterima.');
    }

    /** Petugas: ajukan penyelesaian -> menunggu pemeriksaan + approval. */
    public function submitCompletion(Request $request, Complaint $complaint, ComplaintStatusService $service): RedirectResponse
    {
        abort_unless($request->user()->can('handle', $complaint), 403, 'Anda tidak berwenang menangani laporan ini.');

        $validated = $request->validate([
            'summary' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $complaint, $validated, $service) {
            $complaint->handlingLogs()->create([
                'user_id' => $request->user()->id,
                'title' => 'Pengajuan penyelesaian',
                'note' => $validated['summary'],
            ]);

            $complaint->approvals()->create([
                'requested_by' => $request->user()->id,
                'type' => 'penyelesaian',
                'status' => 'menunggu',
                'request_note' => $validated['summary'],
            ]);

            $service->change($complaint->fresh(), 'menunggu_pemeriksaan', $request->user(), 'Diajukan untuk pemeriksaan hasil.');

            $complaint->assignments()->where('assigned_to', $request->user()->id)->latest()->first()?->update([
                'status' => 'selesai',
                'completed_at' => now(),
            ]);

            ActivityLogger::log('completion_submitted', "Tiket {$complaint->ticket_number} diajukan selesai", Complaint::class, $complaint->id, [], $request);
        });

        return back()->with('success', 'Penyelesaian diajukan untuk pemeriksaan.');
    }
}
