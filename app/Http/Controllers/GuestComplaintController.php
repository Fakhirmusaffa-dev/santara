<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintStatusHistory;
use App\Models\Location;
use App\Notifications\GenericAnnouncement;
use App\Services\ActivityLogger;
use App\Services\ComplaintClassifier;
use App\Services\ComplaintRouter;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'reporter_name' => ['nullable', 'string', 'max:120'],
            'reporter_contact' => ['nullable', 'string', 'max:60'],
            'photos' => ['nullable', 'array', 'max:4'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        // Klasifikasi & prioritas TIDAK diambil dari input pelapor (tidak ada di validasi).
        // Sistem yang menentukan: kategori bertanda khusus (mis. Keamanan) dan/atau isi
        // laporan yang memuat kata kunci berisiko (pembulian, kekerasan, ancaman, dsb).
        // Pelapor tidak punya cara apa pun untuk menurunkan atau menaikkan klasifikasi.
        $classification = app(ComplaintClassifier::class)->classify(
            Category::whereKey($validated['category_id'])->value('is_sensitive') ? 'sensitif' : 'biasa',
            $validated['title'],
            $validated['description'],
        );

        $validated['sensitivity'] = $classification['sensitivity'];
        $validated['priority'] = $classification['priority'];

        // Arahkan langsung ke koordinator divisi kategorinya. Bila ada koordinator
        // aktif, status langsung 'ditugaskan' (melewati verifikasi admin); bila tidak
        // ada koordinator, tetap 'menunggu_verifikasi' untuk admin.
        $router = app(ComplaintRouter::class);
        $koordinatorRole = ComplaintRouter::roleForCategoryId((int) $validated['category_id']);
        $koordinators = $router->recipientsForRole($koordinatorRole);

        $statusAwal = $koordinators->isNotEmpty() ? 'ditugaskan' : 'menunggu_verifikasi';

        $complaint = DB::transaction(function () use ($request, $validated, $koordinators, $statusAwal) {
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
                'status' => $statusAwal,
                'assigned_to' => $koordinators->first()?->id,
            ]);

            $this->storePhotos($request, $complaint);

            // Jejak penugasan otomatis untuk setiap koordinator divisi, supaya
            // "Tugas Saya" masing-masing koordinator terisi.
            foreach ($koordinators as $penerima) {
                $complaint->assignments()->create([
                    'assigned_to' => $penerima->id,
                    'assigned_by' => null,
                    'instruction' => 'Ditugaskan otomatis oleh sistem berdasarkan kategori laporan.',
                    'status' => 'baru',
                ]);
            }

            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status' => null,
                'to_status' => $statusAwal,
                'changed_by' => null,
                'note' => $koordinators->isNotEmpty()
                    ? 'Laporan dibuat tanpa akun dan otomatis diteruskan ke '.$koordinators->first()->roleLabel().'.'
                    : 'Laporan dibuat tanpa akun.',
            ]);

            ActivityLogger::log('complaint_created_guest', "Tiket {$complaint->ticket_number} dibuat tanpa akun", Complaint::class, $complaint->id, [
                'ticket' => $complaint->ticket_number,
            ], $request);

            return $complaint;
        });

        // ---- Distribusi notifikasi ----
        // Penerima utama: koordinator divisi kategorinya. Admin & pimpinan
        // mendapat tembusan pengawasan (metadata saja untuk laporan sensitif).
        foreach ($koordinators as $k) {
            Notify::send(
                $k,
                new GenericAnnouncement(
                    'Laporan baru untuk Anda',
                    "Tiket {$complaint->ticket_number} masuk ke divisi Anda. Segera tindak lanjuti.",
                    route('complaints.show', $complaint),
                    $complaint->ticket_number
                )
            );
        }

        // Tembusan pengawasan untuk admin & pimpinan.
        $this->notifyWatchers($complaint, $koordinators->isNotEmpty());

        return back()->with('guest_ticket', $complaint->ticket_number)
            ->with('success', "Laporan {$complaint->ticket_number} berhasil dikirim. Simpan nomor tiket ini untuk mengecek status laporan.");
    }

    /**
     * Tembusan pengawasan untuk admin & pimpinan agar bisa mengecek dan
     * mengoordinasi laporan. Untuk laporan sensitif, isi tetap tidak bocor:
     * admin diarahkan ke triase buta, pimpinan ke halaman monitoring.
     */
    protected function notifyWatchers(Complaint $complaint, bool $routed): void
    {
        $sensitif = $complaint->sensitivity === 'sensitif';

        foreach (app(ComplaintRouter::class)->watchers() as $watcher) {
            [$title, $message, $url] = match (true) {
                $sensitif && $watcher->isRole('admin') => [
                    'Laporan sensitif masuk',
                    "Tiket {$complaint->ticket_number} membutuhkan penunjukan penangan di Sensitif Masuk.",
                    route('admin.sensitive.index'),
                ],
                $sensitif => [
                    'Laporan baru masuk',
                    "Tiket {$complaint->ticket_number} tercatat untuk dipantau.",
                    route('admin.monitoring'),
                ],
                default => [
                    'Laporan baru masuk',
                    $routed
                        ? "Tiket {$complaint->ticket_number} diteruskan ke koordinator divisinya."
                        : "Laporan baru {$complaint->ticket_number} dari tamu menunggu verifikasi.",
                    $routed ? route('admin.monitoring') : route('complaints.show', $complaint),
                ],
            };

            Notify::send($watcher, new GenericAnnouncement(
                $title,
                $message,
                $url,
                $complaint->ticket_number
            ));
        }
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
