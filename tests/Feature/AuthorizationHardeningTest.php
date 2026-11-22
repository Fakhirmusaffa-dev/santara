<?php

namespace Tests\Feature;

use App\Models\Approval;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintPhoto;
use App\Models\HandlingLog;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\SantaraSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthorizationHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $pelapor;

    protected User $petugas;

    protected User $admin;

    protected User $pimpinan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->seed(SantaraSeeder::class);

        $this->pelapor = User::where('email', 'pelapor@santara.test')->firstOrFail();
        // Penangan = Koordinator Logistik (penerima otomatis laporan divisi logistik).
        $this->petugas = User::where('email', 'logistik@santara.test')->firstOrFail();
        $this->admin = User::where('email', 'admin@santara.test')->firstOrFail();
        $this->pimpinan = User::where('email', 'pimpinan@santara.test')->firstOrFail();
    }

    private function makeComplaint(User $reporter, array $attributes = []): Complaint
    {
        return Complaint::create(array_merge([
            'ticket_number' => 'STR-'.now()->format('Ymd').'-'.strtoupper(bin2hex(random_bytes(6))),
            'user_id' => $reporter->id,
            'category_id' => Category::firstOrFail()->id,
            'location_id' => Location::firstOrFail()->id,
            'title' => 'Keran wastafel asrama bocor',
            'description' => 'Keran wastafel asrama putra bocor sejak dua hari dan menggenangi lantai.',
            'priority' => 'sedang',
            'sensitivity' => 'biasa',
            'status' => 'menunggu_verifikasi',
        ], $attributes));
    }

    public function test_petugas_tidak_bisa_mengubah_status_lewat_jalur_admin(): void
    {
        $complaint = $this->makeComplaint($this->pelapor, [
            'assigned_to' => $this->petugas->id,
            'status' => 'ditugaskan',
        ]);

        $this->actingAs($this->petugas)
            ->post(route('complaints.status', $complaint), ['to_status' => 'selesai'])
            ->assertForbidden();

        $this->assertSame('ditugaskan', $complaint->fresh()->status);
    }

    public function test_admin_tidak_bisa_mengubah_status_laporan_sensitif_tanpa_akses(): void
    {
        $complaint = $this->makeComplaint($this->pelapor, [
            'sensitivity' => 'sensitif',
            'status' => 'terverifikasi',
        ]);

        $this->actingAs($this->admin)
            ->post(route('complaints.status', $complaint), ['to_status' => 'ditolak'])
            ->assertForbidden();

        $this->assertSame('terverifikasi', $complaint->fresh()->status);
    }

    public function test_daftar_persetujuan_tidak_membocorkan_laporan_sensitif(): void
    {
        $biasa = $this->makeComplaint($this->pelapor, ['title' => 'Judul biasa terlihat']);
        $sensitif = $this->makeComplaint($this->pelapor, [
            'title' => 'Judul rahasia tidak boleh tampil',
            'sensitivity' => 'sensitif',
        ]);

        foreach ([$biasa, $sensitif] as $complaint) {
            Approval::create([
                'complaint_id' => $complaint->id,
                'requested_by' => $this->petugas->id,
                'type' => 'penyelesaian',
                'status' => 'menunggu',
                'request_note' => 'Selesai dikerjakan.',
            ]);
        }

        $this->actingAs($this->pimpinan)
            ->get(route('approvals.index'))
            ->assertOk()
            ->assertSee($biasa->ticket_number)
            ->assertDontSee('Judul rahasia tidak boleh tampil');
    }

    public function test_lampiran_penanganan_hanya_bisa_diakses_jika_tercatat(): void
    {
        Storage::fake('local');

        $complaint = $this->makeComplaint($this->pelapor, [
            'assigned_to' => $this->petugas->id,
            'status' => 'sedang_ditangani',
        ]);

        // File "nyasar" di folder penanganan tanpa catatan handling log.
        Storage::disk('local')->put('complaints/'.$complaint->id.'/handling/orphan.jpg', 'isi');

        $this->actingAs($this->admin)
            ->get(route('attachments.handling', ['path' => 'complaints/'.$complaint->id.'/handling/orphan.jpg']))
            ->assertNotFound();

        // File yang tercatat di log tetap bisa diakses pihak berhak.
        Storage::disk('local')->put('complaints/'.$complaint->id.'/handling/bukti.jpg', 'isi');
        HandlingLog::create([
            'complaint_id' => $complaint->id,
            'user_id' => $this->petugas->id,
            'note' => 'Sudah diperbaiki.',
            'photo_path' => 'complaints/'.$complaint->id.'/handling/bukti.jpg',
        ]);

        $this->actingAs($this->admin)
            ->get(route('attachments.handling', ['path' => 'complaints/'.$complaint->id.'/handling/bukti.jpg']))
            ->assertOk();
    }

    public function test_pelapor_tidak_bisa_menghapus_laporan_yang_sudah_diproses(): void
    {
        $complaint = $this->makeComplaint($this->pelapor, ['status' => 'selesai']);

        $this->actingAs($this->pelapor)
            ->delete(route('complaints.destroy', $complaint))
            ->assertForbidden();

        $this->assertNotSoftDeleted($complaint);
    }

    public function test_lampiran_foto_laporan_lain_ditolak(): void
    {
        Storage::fake('local');

        $milikOrangLain = $this->makeComplaint($this->admin);
        $foto = ComplaintPhoto::create([
            'complaint_id' => $milikOrangLain->id,
            'uploaded_by' => $this->admin->id,
            'path' => 'complaints/'.$milikOrangLain->id.'/foto.jpg',
            'original_name' => 'foto.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 10,
            'kind' => 'laporan',
        ]);
        Storage::disk('local')->put($foto->path, 'isi');

        $this->actingAs($this->petugas)
            ->get(route('attachments.show', $foto))
            ->assertForbidden();
    }
}
