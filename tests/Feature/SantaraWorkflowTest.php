<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\SantaraSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SantaraWorkflowTest extends TestCase
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
        $this->petugas = User::where('email', 'petugas@santara.test')->firstOrFail();
        $this->admin = User::where('email', 'admin@santara.test')->firstOrFail();
        $this->pimpinan = User::where('email', 'pimpinan@santara.test')->firstOrFail();
    }

    public function test_guest_can_open_landing_page_and_submit_report_without_logging_in(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Buat laporan')
            ->assertSee('Cek status laporan')
            ->assertSee('Masuk staf');

        $response = $this->from(route('guest.complaints.create'))
            ->post(route('guest.complaints.store'), [
                'category_id' => Category::firstOrFail()->id,
                'location_id' => Location::firstOrFail()->id,
                'title' => 'Keran asrama bocor parah',
                'description' => 'Keran wastafel asrama putra bocor sejak dua hari dan menggenangi lantai.',
                'priority' => 'tinggi',
                'sensitivity' => 'biasa',
            ]);

        $complaint = Complaint::firstOrFail();

        $response->assertRedirect(route('guest.complaints.create'))
            ->assertSessionHas('guest_ticket', $complaint->ticket_number);
        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'user_id' => null,
            'title' => 'Keran asrama bocor parah',
        ]);
        $this->assertGuest();
    }

    public function test_login_redirects_by_role(): void
    {
        foreach (
            [
                [$this->pelapor, 'pelapor.dashboard'],
                [$this->petugas, 'petugas.dashboard'],
                [$this->admin, 'admin.dashboard'],
                [$this->pimpinan, 'pimpinan.dashboard'],
            ] as [$user, $route]
        ) {
            $response = $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'password',
            ]);
            $response->assertRedirect(route($route));
            $this->post(route('logout'));
        }
    }

    public function test_pelapor_can_create_and_view_own_complaint(): void
    {
        $cat = Category::firstOrFail();
        $loc = Location::firstOrFail();

        $response = $this->actingAs($this->pelapor)->post(route('complaints.store'), [
            'category_id' => $cat->id,
            'location_id' => $loc->id,
            'title' => 'Keran asrama bocor parah',
            'description' => 'Keran wastafel asrama putra blok A bocor sejak dua hari dan menggenangi lantai.',
            'priority' => 'tinggi',
            'sensitivity' => 'biasa',
        ]);

        $complaint = Complaint::firstOrFail();
        $response->assertRedirect(route('complaints.show', $complaint));
        $this->assertStringStartsWith('STR-', $complaint->ticket_number);
        $this->assertEquals('menunggu_verifikasi', $complaint->status);

        // Pelapor lain tidak boleh melihat (IDOR).
        $other = User::create([
            'name' => 'Pelapor Lain',
            'email' => 'lain@santara.test',
            'password' => 'password',
            'role' => 'pelapor',
            'is_active' => true,
        ]);
        $this->actingAs($other)->get(route('complaints.show', $complaint))->assertForbidden();
    }

    public function test_full_workflow_verify_assign_handle_complete_feedback(): void
    {
        $this->actingAs($this->pelapor)->post(route('complaints.store'), [
            'category_id' => Category::first()->id,
            'location_id' => Location::first()->id,
            'title' => 'Lampu kelas mati total',
            'description' => 'Lampu ruang kelas 1 mati total sejak kemarin sore sehingga KBM terganggu.',
            'priority' => 'sedang',
            'sensitivity' => 'biasa',
        ]);
        $complaint = Complaint::firstOrFail();

        // Verifikasi + tolak-butuh-alasan.
        $this->actingAs($this->admin)
            ->post(route('complaints.reject', $complaint), ['rejection_reason' => 'pendek'])
            ->assertSessionHasErrors('rejection_reason');

        $this->actingAs($this->admin)
            ->post(route('complaints.verify', $complaint), ['verification_note' => 'Valid.'])
            ->assertSessionHasNoErrors();
        $this->assertEquals('terverifikasi', $complaint->fresh()->status);

        // Konflik kepentingan: petugas tidak boleh menangani laporannya sendiri.
        $selfReport = Complaint::create([
            'ticket_number' => 'STR-TEST-SELF',
            'user_id' => $this->petugas->id,
            'category_id' => Category::first()->id,
            'title' => 'Uji konflik yang cukup panjang',
            'description' => 'Deskripsi yang cukup panjang untuk lolos validasi minimal dua puluh karakter.',
            'priority' => 'sedang',
            'sensitivity' => 'biasa',
            'status' => 'terverifikasi',
        ]);
        $this->actingAs($this->admin)->post(route('complaints.assign', $selfReport), [
            'assigned_to' => $this->petugas->id,
            'instruction' => 'Instruksi penanganan yang jelas dan cukup panjang.',
        ])->assertSessionHasErrors('assigned_to');

        // Penugasan normal.
        $this->actingAs($this->admin)->post(route('complaints.assign', $complaint), [
            'assigned_to' => $this->petugas->id,
            'instruction' => 'Periksa instalasi listrik kelas 1 dan ganti lampu yang rusak.',
        ])->assertSessionHasNoErrors();
        $this->assertEquals('ditugaskan', $complaint->fresh()->status);

        // Petugas progres + ajukan selesai.
        $this->actingAs($this->petugas)->post(route('complaints.accept', $complaint))->assertSessionHasNoErrors();
        $this->actingAs($this->petugas)->post(route('complaints.handling', $complaint), [
            'title' => 'Penggantian lampu',
            'note' => 'Lampu diganti dan instalasi diperiksa, berfungsi normal.',
            'progress' => 80,
        ])->assertSessionHasNoErrors();
        $this->assertEquals('sedang_ditangani', $complaint->fresh()->status);

        $this->actingAs($this->petugas)->post(route('complaints.complete', $complaint), [
            'summary' => 'Semua lampu sudah menyala normal dan sudah diuji.',
        ])->assertSessionHasNoErrors();
        $this->assertEquals('menunggu_pemeriksaan', $complaint->fresh()->status);

        // Pimpinan menyetujui.
        $approval = $complaint->fresh()->approvals()->firstOrFail();
        $this->actingAs($this->pimpinan)->post(route('approvals.decide', $approval), [
            'decision' => 'disetujui',
            'decision_note' => 'Hasil baik, selesai.',
        ])->assertSessionHasNoErrors();
        $this->assertEquals('selesai', $complaint->fresh()->status);

        // Feedback hanya setelah selesai.
        $this->actingAs($this->pelapor)->post(route('feedbacks.store', $complaint), [
            'rating' => 5,
            'comment' => 'Cepat dan rapi.',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('feedbacks', ['complaint_id' => $complaint->id, 'rating' => 5]);

        // Transisi ilegal ditolak backend.
        $this->actingAs($this->admin)->post(route('complaints.status', $complaint), [
            'to_status' => 'ditugaskan',
        ])->assertSessionHasErrors('to_status');
    }

    public function test_pengajuan_selesai_dari_status_ditugaskan(): void
    {
        $this->actingAs($this->pelapor)->post(route('complaints.store'), [
            'category_id' => Category::first()->id,
            'location_id' => Location::first()->id,
            'title' => 'Kran asrama putri rusak',
            'description' => 'Kran air asrama putri rusak sehingga air terus mengalir dan terbuang sia-sia.',
            'priority' => 'sedang',
            'sensitivity' => 'biasa',
        ]);
        $complaint = Complaint::firstOrFail();

        $this->actingAs($this->admin)->post(route('complaints.verify', $complaint), ['verification_note' => 'Valid.'])->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post(route('complaints.assign', $complaint), [
            'assigned_to' => $this->petugas->id,
            'instruction' => 'Periksa kran yang rusak dan ganti bila perlu.',
        ])->assertSessionHasNoErrors();
        $this->assertEquals('ditugaskan', $complaint->fresh()->status);

        $this->actingAs($this->petugas)->post(route('complaints.accept', $complaint))->assertSessionHasNoErrors();

        // Pengajuan penyelesaian langsung dari 'ditugaskan' tidak boleh melempar InvalidArgumentException.
        $this->actingAs($this->petugas)->post(route('complaints.complete', $complaint), [
            'summary' => 'Kran sudah diganti dan tidak ada kebocoran lagi.',
        ])->assertSessionHasNoErrors();

        $this->assertEquals('menunggu_pemeriksaan', $complaint->fresh()->status);
        $this->assertDatabaseHas('approvals', [
            'complaint_id' => $complaint->id,
            'type' => 'penyelesaian',
            'status' => 'menunggu',
        ]);
        $this->assertDatabaseHas('assignments', [
            'complaint_id' => $complaint->id,
            'assigned_to' => $this->petugas->id,
            'status' => 'selesai',
        ]);
    }

    public function test_sensitive_report_is_isolated(): void
    {
        $this->actingAs($this->pelapor)->post(route('complaints.store'), [
            'category_id' => Category::first()->id,
            'location_id' => Location::first()->id,
            'title' => 'Laporan pribadi yang perlu dibatasi aksesnya',
            'description' => 'Isi laporan sensitif yang hanya boleh dibaca pihak yang ditunjuk institusi.',
            'priority' => 'tinggi',
            'sensitivity' => 'sensitif',
        ]);
        $complaint = Complaint::firstOrFail();

        // Admin tanpa akses eksplisit: ditolak.
        $this->actingAs($this->admin)->get(route('complaints.show', $complaint))->assertForbidden();
        // Pimpinan tanpa akses eksplisit: ditolak.
        $this->actingAs($this->pimpinan)->get(route('complaints.show', $complaint))->assertForbidden();

        // Admin memberi akses ke pimpinan.
        $this->actingAs($this->admin)->post(route('complaints.grant-access', $complaint), [
            'user_id' => $this->pimpinan->id,
            'reason' => 'Evaluasi pimpinan atas kasus sensitif.',
        ])->assertSessionHasNoErrors();

        $this->actingAs($this->pimpinan)->get(route('complaints.show', $complaint))->assertOk();
    }
}

class SecurityHeaderTest extends TestCase
{
    public function test_halaman_publik_tidak_memakai_atribut_style_inline(): void
    {
        $this->withoutVite();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('style=', false)
            ->assertSee('auth-backdrop');

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('style=', false)
            ->assertSee('auth-backdrop');
    }

    public function test_csp_memblokir_style_inline_saat_penguatan_aktif(): void
    {
        config(['app.drop_style_attributes' => true]);

        $csp = $this->get(route('login'))->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("style-src 'self' https://fonts.googleapis.com", $csp);
        $this->assertStringNotContainsString('unsafe-inline', $csp);
        $this->assertStringContainsString("script-src 'self' 'nonce-", $csp);
    }
}
