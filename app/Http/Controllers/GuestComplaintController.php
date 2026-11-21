<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintStatusHistory;
use App\Models\Location;
use App\Models\User;
use App\Notifications\GenericAnnouncement;
use App\Services\ActivityLogger;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GuestComplaintController extends Controller
{
    public function create(): View
    {
        return view('complaints.guest-create', [
            'categories' => Category::active()->orderBy('name')->get(),
            'locations' => Location::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'title' => ['required', 'string', 'min:8', 'max:180'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'priority' => ['required', Rule::in(Complaint::PRIORITIES)],
            'sensitivity' => ['required', Rule::in(Complaint::SENSITIVITIES)],
            'reporter_name' => ['nullable', 'string', 'max:120'],
            'reporter_contact' => ['nullable', 'string', 'max:60'],
            'photos' => ['nullable', 'array', 'max:4'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $complaint = DB::transaction(function () use ($request, $validated) {
            $complaint = Complaint::create([
                'ticket_number' => $this->generateTicketNumber(),
                'user_id' => null,
                'reporter_name' => $validated['reporter_name'] ?? null,
                'reporter_contact' => $validated['reporter_contact'] ?? null,
                'category_id' => $validated['category_id'],
                'location_id' => $validated['location_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'priority' => $validated['priority'],
                'sensitivity' => $validated['sensitivity'],
                'status' => 'menunggu_verifikasi',
            ]);

            $this->storePhotos($request, $complaint);

            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status' => null,
                'to_status' => 'menunggu_verifikasi',
                'changed_by' => null,
                'note' => 'Laporan dibuat tanpa akun.',
            ]);

            ActivityLogger::log('complaint_created_guest', "Tiket {$complaint->ticket_number} dibuat tanpa akun", Complaint::class, $complaint->id, [
                'ticket' => $complaint->ticket_number,
            ], $request);

            return $complaint;
        });

        // Beri tahu admin & pimpinan bahwa ada laporan baru masuk.
        $recipients = User::where('is_active', true)->whereIn('role', ['admin', 'pimpinan'])->get();
        foreach ($recipients as $recipient) {
            Notify::send(
                $recipient,
                new GenericAnnouncement(
                    'Laporan baru masuk',
                    "Laporan baru {$complaint->ticket_number} dari tamu menunggu verifikasi.",
                    route('complaints.show', $complaint),
                    $complaint->ticket_number
                )
            );
        }

        // Triase buta: beri tahu semua admin aktif bahwa ada tiket sensitif
        // yang butuh penunjukan penangan — TANPA menyalin isi laporan.
        if ($complaint->sensitivity === 'sensitif') {
            $admins = User::where('role', 'admin')->where('is_active', true)->get();
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

        return back()->with('guest_ticket', $complaint->ticket_number)
            ->with('success', "Laporan {$complaint->ticket_number} berhasil dikirim. Simpan nomor tiket ini untuk mengecek status laporan.");
    }

    /** Tamu: cek status laporan lewat nomor tiket. */
    public function track(Request $request): View
    {
        $validated = $request->validate([
            'ticket' => ['nullable', 'string', 'max:24', 'regex:/^STR-[0-9]{8}-[A-F0-9]{12}$/'],
        ]);
        $ticket = strtoupper(trim((string) ($validated['ticket'] ?? '')));
        $complaint = null;

        if ($ticket !== '') {
            $complaint = Complaint::query()
                ->where('ticket_number', $ticket)
                ->whereNull('user_id')
                ->first();

            if ($complaint) {
                $complaint->load(['category', 'location', 'statusHistories.changer']);
            }
        }

        return view('complaints.guest-track', [
            'ticket' => $ticket,
            'complaint' => $complaint,
        ]);
    }

    protected function generateTicketNumber(): string
    {
        do {
            $ticket = 'STR-'.now()->format('Ymd').'-'.strtoupper(bin2hex(random_bytes(6)));
        } while (Complaint::where('ticket_number', $ticket)->exists());

        return $ticket;
    }

    protected function storePhotos(Request $request, Complaint $complaint): void
    {
        if (! $request->hasFile('photos')) {
            return;
        }

        foreach ((array) $request->file('photos') as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $path = $file->store('complaints/'.$complaint->id, 'local');
            $complaint->photos()->create([
                'uploaded_by' => null,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'kind' => 'laporan',
            ]);
        }
    }
}
