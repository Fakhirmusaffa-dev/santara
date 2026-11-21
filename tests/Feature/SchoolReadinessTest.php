<?php

namespace Tests\Feature;

use App\Models\Approval;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Simulasi pemakaian nyata di sekolah, semua role + semua fitur penting.
 */
class SchoolReadinessTest extends TestCase
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
        $this->seed(\Database\Seeders\SantaraSeeder::class);

        $this->pelapor = User::where('email', 'pelapor@santara.test')->firstOrFail();
        $this->petugas = User::where('email', 'petugas@santara.test')->firstOrFail();
        $this->admin = User::where('email', 'admin@santara.test')->firstOrFail();
        $this->pimpinan = User::where('email', 'pimpinan@santara.test')->firstOrFail();
    }

    public function test_semua_dashboard_bisa_dibuka(): void
    {
        $this->actingAs($this->pelapor)->get(route('pelapor.dashboard'))->assertOk();
        $this->actingAs($this->petugas)->get(route('petugas.dashboard'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($this->pimpinan)->get(route('pimpinan.dashboard'))->assertOk();
    }

    public function test_alur_lengkap_sekolah_dari_lapor_sampai_dinilai(): void
    {
        Storage::fake('local');

        // 1. Santri melapor + foto.
        $foto = UploadedFile::fake()->image('kerusakan.jpg', 800, 600);
        $this->actingAs($this->pelapor)->post(route('complaints.store'), [
            'category_id' => Category::first()->id,
            'location_id' => Location::first()->id,
            'title' => 'Keran asrama putra blok A bocor deras',
            'description' => 'Keran wastafel asrama putra blok A bocor deras sejak tadi malam dan menggenangi lantai kamar mandi.',
            'priority' => 'tinggi',
            'sensitivity' => 'biasa',
            'photos' => [$foto],
        ])->assertRedirect();
        $complaint = Complaint::latest()->firstOrFail();
        $this->assertEquals('menunggu_verifikasi', $complaint->status);
        $this->assertCount(1, $complaint->photos);

        // Lampiran bisa diunduh pelapor.
        $this->actingAs($this->pelapor)->get(route('attachments.show', $complaint->photos->first()))->assertOk();

        // 2. Admin verifikasi + tugaskan ke petugas (+konflik kepentingan dicegah).
        $this->actingAs($this->admin)->post(route('complaints.verify', $complaint), ['verification_note' => 'Valid, segera tangani.'])->assertRedirect();
        $this->assertEquals('terverifikasi', $complaint->fresh()->status);

        $this->actingAs($this->admin)->post(route('complaints.assign', $complaint), [
            'assigned_to' => $this->petugas->id,
            'instruction' => 'Ganti keran wastafel asrama putra blok A hari ini juga.',
        ])->assertRedirect();
        $this->assertEquals('ditugaskan', $complaint->fresh()->status);

        // 3. Petugas terima + catat progres + dokumentasi + ajukan selesai.
        $this->actingAs($this->petugas)->post(route('complaints.accept', $complaint))->assertRedirect();
        $bukti = UploadedFile::fake()->image('sesudah.jpg', 800, 600);
        $this->actingAs($this->petugas)->post(route('complaints.handling', $complaint), [
            'title' => 'Penggantian keran',
            'note' => 'Keran lama dilepas, keran baru dipasang dan sudah dites tidak bocor.',
            'progress' => 100,
            'photo' => $bukti,
        ])->assertRedirect();
        $this->assertEquals('sedang_ditangani', $complaint->fresh()->status);
        $this->assertNotNull($complaint->fresh()->handlingLogs()->first()->photo_path);

        $this->actingAs($this->petugas)->post(route('complaints.complete', $complaint), [
            'summary' => 'Keran sudah diganti dan lantai sudah dikeringkan.',
        ])->assertRedirect();
        $this->assertEquals('menunggu_pemeriksaan', $complaint->fresh()->status);

        // 4. Pimpinan setujui di halaman persetujuan -> selesai.
        $approval = $complaint->fresh()->approvals()->firstOrFail();
        $this->actingAs($this->pimpinan)->get(route('approvals.index'))->assertOk();
        $this->actingAs($this->pimpinan)->post(route('approvals.decide', $approval), [
            'decision' => 'disetujui',
            'decision_note' => 'Hasil bagus, ditutup.',
        ])->assertRedirect();
        $this->assertEquals('selesai', $complaint->fresh()->status);

        // 5. Santri memberi nilai.
        $this->actingAs($this->pelapor)->post(route('feedbacks.store', $complaint), [
            'rating' => 5,
            'comment' => 'Cepat ditangani, terima kasih.',
        ])->assertRedirect();
        $this->assertDatabaseHas('feedbacks', ['complaint_id' => $complaint->id, 'rating' => 5]);

        // 6. Feedback ganda ditolak (422) — bukan error 500.
        $this->actingAs($this->pelapor)->post(route('feedbacks.store', $complaint), [
            'rating' => 4,
        ])->assertStatus(422);

        // 7. Notifikasi terkirim ke pelapor & petugas.
        $this->assertTrue($this->pelapor->notifications()->count() >= 2);
        $this->actingAs($this->pelapor)->get(route('notifications.index'))->assertOk();
        $this->actingAs($this->pelapor)->post(route('notifications.read-all'))->assertRedirect();

        // 8. Laporan administratif + ekspor CSV jalan + data sensitif disamarkan.
        $this->actingAs($this->admin)->get(route('reports.index'))->assertOk();
        $csv = $this->actingAs($this->admin)->get(route('reports.export'))->assertOk();
        $this->assertStringContainsString('text/csv', $csv->headers->get('Content-Type'));
        $this->assertStringContainsString($complaint->ticket_number, $csv->streamedContent());
        $this->actingAs($this->pimpinan)->get(route('reports.index'))->assertOk();
        $this->actingAs($this->pelapor)->get(route('reports.index'))->assertForbidden();
    }

    public function test_petugas_hanya_melihat_tugasnya_dan_akses_ditolak_dengan_403(): void
    {
        $milik = Complaint::create([
            'ticket_number' => 'STR-TUGAS-001',
            'user_id' => $this->pelapor->id,
            'category_id' => Category::first()->id,
            'title' => 'Laporan tugas petugas yang cukup panjang',
            'description' => 'Deskripsi laporan tugas petugas yang cukup panjang untuk validasi.',
            'priority' => 'sedang',
            'sensitivity' => 'biasa',
            'status' => 'ditugaskan',
            'assigned_to' => $this->petugas->id,
        ]);
        $lain = Complaint::create([
            'ticket_number' => 'STR-LAIN-001',
            'user_id' => $this->pelapor->id,
            'category_id' => Category::first()->id,
            'title' => 'Laporan lain yang bukan tugas petugas ini',
            'description' => 'Deskripsi laporan lain yang bukan tugas petugas ini, cukup panjang.',
            'priority' => 'rendah',
            'sensitivity' => 'biasa',
            'status' => 'terverifikasi',
        ]);

        $this->actingAs($this->petugas)->get(route('complaints.show', $milik))->assertOk();
        $this->actingAs($this->petugas)->get(route('complaints.show', $lain))->assertForbidden();
        // Ubah status ilegal langsung via POST juga ditolak sesuai policy (403), bukan 500.
        $this->actingAs($this->petugas)->post(route('complaints.status', $lain), ['to_status' => 'selesai'])->assertForbidden();
    }

    public function test_laporan_sensitif_tetap_terisolasi_ujung_ke_ujung(): void
    {
        Storage::fake('local');
        $this->actingAs($this->pelapor)->post(route('complaints.store'), [
            'category_id' => Category::first()->id,
            'location_id' => Location::first()->id,
            'title' => 'Laporan pribadi yang perlu dibatasi aksesnya',
            'description' => 'Isi laporan sensitif yang hanya boleh dibaca pihak yang ditunjuk institusi sekolah.',
            'priority' => 'tinggi',
            'sensitivity' => 'sensitif',
            'photos' => [UploadedFile::fake()->image('rahasia.jpg')],
        ])->assertRedirect();
        $complaint = Complaint::latest()->firstOrFail();
        $foto = $complaint->photos()->firstOrFail();

        // Admin & pimpinan tanpa akses: halaman + file sama-sama 403.
        $this->actingAs($this->admin)->get(route('complaints.show', $complaint))->assertForbidden();
        $this->actingAs($this->admin)->get(route('attachments.show', $foto))->assertForbidden();
        $this->actingAs($this->pimpinan)->get(route('complaints.show', $complaint))->assertForbidden();

        // Admin memberi akses ke pimpinan -> keduanya bisa buka halaman + file.
        $this->actingAs($this->admin)->post(route('complaints.grant-access', $complaint), [
            'user_id' => $this->pimpinan->id,
            'reason' => 'Evaluasi pimpinan atas kasus sensitif.',
        ])->assertRedirect();
        $this->actingAs($this->pimpinan)->get(route('complaints.show', $complaint))->assertOk();
        $this->actingAs($this->pimpinan)->get(route('attachments.show', $foto))->assertOk();

        // Ekspor CSV menyamarkan judul sensitif.
        $csv = $this->actingAs($this->pimpinan)->get(route('reports.export'))->assertOk();
        $this->assertStringContainsString('[TERBATAS]', $csv->streamedContent());
    }

    public function test_profil_ganti_password_dan_login_ulang(): void
    {
        // Ubah profil.
        $this->actingAs($this->pelapor)->put(route('profile.update'), [
            'name' => 'Ahmad Santri Baru',
            'phone' => '08123456789',
            'position' => 'Santri Kelas 1',
        ])->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $this->pelapor->id, 'name' => 'Ahmad Santri Baru']);

        // Ganti password lalu login ulang dengan password baru.
        // Aturan Password::min(12) wajib huruf besar+kecil+angka+simbol.
        $passwordBaru = 'Password-baru-123!';
        $this->actingAs($this->pelapor)->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => $passwordBaru,
            'password_confirmation' => $passwordBaru,
        ])->assertRedirect();
        $this->post(route('logout'));
        $this->post(route('login.store'), ['email' => 'pelapor@santara.test', 'password' => $passwordBaru])
            ->assertRedirect(route('pelapor.dashboard'));
    }

    public function test_akun_nonaktif_tidak_bisa_login(): void
    {
        $this->pelapor->update(['is_active' => false]);
        $this->post(route('login.store'), ['email' => 'pelapor@santara.test', 'password' => 'password'])
            ->assertSessionHasErrors('email');
    }
}
