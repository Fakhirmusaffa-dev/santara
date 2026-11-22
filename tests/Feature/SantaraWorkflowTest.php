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

    protected User $logistik;

    protected User $keamanan;

    protected User $admin;

    protected User $pimpinan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->seed(SantaraSeeder::class);

        $this->pelapor = User::where('email', 'pelapor@santara.test')->firstOrFail();
        $this->logistik = User::where('email', 'logistik@santara.test')->firstOrFail();
        $this->keamanan = User::where('email', 'keamanan@santara.test')->firstOrFail();
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

    public function test_system_classifies_complaint_and_reporter_cannot_override_it(): void
    {
        $umum = Category::firstOrFail(); // kategori umum dari seeder
        $keamanan = Category::create([
            'name' => 'Keamanan Uji',
            'slug' => 'keamanan-uji',
            'is_active' => true,
            'is_sensitive' => true,
        ]);

        $base = [
            'location_id' => Location::firstOrFail()->id,
            'title' => 'Keran asrama bocor parah',
            'description' => 'Keran wastafel asrama putra bocor sejak dua hari dan menggenangi lantai.',
        ];

        // 1. Kategori Keamanan (pembulian): pelapor mencoba menyamarkan jadi 'biasa'
        //    dan prioritas 'rendah' -> sistem tetap menetapkan sensitif + mendesak.
        //    (Array spesifik ditaruh di kanan `+` agar menang atas nilai dasar.)
        $this->post(route('guest.complaints.store'), [
            'category_id' => $keamanan->id,
            'title' => 'Pembulian di asrama putra blok A',
            'sensitivity' => 'biasa',
            'priority' => 'rendah',
        ] + $base)->assertRedirect();

        $this->assertDatabaseHas('complaints', [
            'title' => 'Pembulian di asrama putra blok A',
            'sensitivity' => 'sensitif',
            'priority' => 'mendesak',
        ]);

        // 2. Kategori umum + tidak ada kata kunci berisiko -> biasa + sedang.
        $this->post(route('guest.complaints.store'), [
            'category_id' => $umum->id,
            'sensitivity' => 'sensitif',
            'priority' => 'mendesak',
        ] + $base)->assertRedirect();

        $biasa = Complaint::latest('id')->firstOrFail();
        $this->assertSame('biasa', $biasa->sensitivity);
        $this->assertSame('sedang', $biasa->priority);
    }

    public function test_risk_keywords_make_a_general_category_sensitive(): void
    {
        $umum = Category::firstOrFail();

        foreach (['Saya di-bully teman sekelas', 'Ada ancaman dan pemukulan di asrama'] as $judul) {
            $this->post(route('guest.complaints.store'), [
                'category_id' => $umum->id,
                'location_id' => Location::firstOrFail()->id,
                'title' => $judul,
                'description' => 'Kejadian ini berulang dan saya sudah tidak berani masuk kelas.',
            ])->assertRedirect();

            $complaint = Complaint::latest('id')->firstOrFail();
            $this->assertSame('sensitif', $complaint->sensitivity, "Gagal mendeteksi: {$judul}");
            $this->assertSame('mendesak', $complaint->priority);
        }
    }

    public function test_login_redirects_by_role(): void
    {
        foreach (
            [
                [$this->pelapor, 'pelapor.dashboard'],
                [$this->logistik, 'koordinator.dashboard'],
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
        // Kategori sarana & prasarana langsung diteruskan ke Koordinator Logistik.
        $this->assertEquals('ditugaskan', $complaint->status);
        $this->assertNotNull($complaint->assigned_to);

        // Pelapor lain tidak boleh melihat (IDOR).
        $other = User::create([
            'name' => 'Pelapor Lain',
            'email' => 'lain@santara.test',
            'password' => 'password',
            'role' => 'pelapor',
            'is_active' => true,
        ]);
        // Penyamaran sumber daya: pelapor lain mendapat 404 (bukan petunjuk tiket ini ada).
        $this->actingAs($other)->get(route('complaints.show', $complaint))->assertNotFound();
    }

    public function test_full_workflow_verify_assign_handle_complete_feedback(): void
    {
        // Order default memakai slug, jadi pastikan targetnya kategori sarana & prasarana.
        $sarana = Category::where('slug', 'kerusakan-fasilitas')->firstOrFail();
        $this->actingAs($this->pelapor)->post(route('complaints.store'), [
            'category_id' => $sarana->id,
            'location_id' => Location::first()->id,
            'title' => 'Lampu kelas mati total',
            'description' => 'Lampu ruang kelas 1 mati total sejak kemarin sore sehingga KBM terganggu.',
            'priority' => 'sedang',
            'sensitivity' => 'biasa',
        ]);
        $complaint = Complaint::firstOrFail();

        // Laporan sudah otomatis di tangan Koordinator Logistik; admin tetap bisa
        // mengubah status terkontrol (tidak ada verifikasi manual lagi).
        $this->assertEquals('ditugaskan', $complaint->fresh()->status);

        // Penugasan manual oleh admin ke koordinator aktif berjalan normal.
        $sendiri = User::create([
            'name' => 'Koordinator Mandiri',
            'email' => 'mandiri@santara.test',
            'password' => 'password',
            'role' => 'koordinator_logistik',
            'is_active' => true,
        ]);
        $siapTugaskan = Complaint::create([
            'ticket_number' => 'STR-TEST-MANUAL',
            'user_id' => $this->pelapor->id,
            'category_id' => $sarana->id,
            'title' => 'Penugasan manual oleh admin',
            'description' => 'Deskripsi yang cukup panjang untuk lolos validasi minimal dua puluh karakter.',
            'priority' => 'sedang',
            'sensitivity' => 'biasa',
            'status' => 'terverifikasi',
        ]);
        $this->actingAs($this->admin)->post(route('complaints.assign', $siapTugaskan), [
            'assigned_to' => $sendiri->id,
            'instruction' => 'Instruksi penanganan yang jelas dan cukup panjang.',
        ])->assertRedirect();
        $this->assertEquals($sendiri->id, $siapTugaskan->fresh()->assigned_to);

        // Koordinator Logistik (penerima otomatis) menerima tugas, mencatat progres,
        // lalu mengajukan penyelesaian.
        $penangan = $this->logistik;
        $this->actingAs($penangan)->post(route('complaints.accept', $complaint))->assertSessionHasNoErrors();
        $this->actingAs($penangan)->post(route('complaints.handling', $complaint), [
            'title' => 'Penggantian lampu',
            'note' => 'Lampu diganti dan instalasi diperiksa, berfungsi normal.',
            'progress' => 80,
        ])->assertSessionHasNoErrors();
        $this->assertSame($penangan->id, (int) $complaint->fresh()->assigned_to);
        $this->assertEquals('sedang_ditangani', $complaint->fresh()->status);

        $this->actingAs($penangan)->post(route('complaints.complete', $complaint), [
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

        // Laporan otomatis ditugaskan; koordinator penerima langsung menerima.
        $this->assertEquals('ditugaskan', $complaint->fresh()->status);
        $penerima = $complaint->fresh()->assignee;
        $this->assertNotNull($penerima);
        $this->actingAs($penerima)->post(route('complaints.accept', $complaint))->assertSessionHasNoErrors();

        // Pengajuan penyelesaian langsung dari 'ditugaskan' tidak boleh melempar InvalidArgumentException.
        $this->actingAs($penerima)->post(route('complaints.complete', $complaint), [
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
            'assigned_to' => $penerima->id,
            'status' => 'selesai',
        ]);
    }

    public function test_sensitive_report_is_isolated(): void
    {
        $this->actingAs($this->pelapor)->post(route('complaints.store'), [
            'category_id' => Category::first()->id,
            'location_id' => Location::first()->id,
            'title' => 'Laporan pribadi yang perlu dibatasi aksesnya',
            'description' => 'Isi laporan sensitif berisi ancaman yang hanya boleh dibaca pihak yang ditunjuk institusi.',
        ]);
        $complaint = Complaint::firstOrFail();

        // Sistem (bukan pelapor) yang menetapkan sensitif berdasarkan isi laporan.
        $this->assertSame('sensitif', $complaint->sensitivity);

        // Admin tanpa akses eksplisit: ditolak (404 agar keberadaan tiket tidak terbocor).
        $this->actingAs($this->admin)->get(route('complaints.show', $complaint))->assertNotFound();
        // Pimpinan tanpa akses eksplisit: ditolak.
        $this->actingAs($this->pimpinan)->get(route('complaints.show', $complaint))->assertNotFound();

        // Admin memberi akses ke pimpinan.
        $this->actingAs($this->admin)->post(route('complaints.grant-access', $complaint), [
            'user_id' => $this->pimpinan->id,
            'reason' => 'Evaluasi pimpinan atas kasus sensitif.',
        ])->assertSessionHasNoErrors();

        $this->actingAs($this->pimpinan)->get(route('complaints.show', $complaint))->assertOk();
    }

    public function test_laporan_dikirim_ke_koordinator_sesuai_kategori(): void
    {
        // Bersihkan notifikasi bawaan agar hitungan murni milik laporan ini.
        User::query()->each(fn (User $u) => $u->notifications()->delete());

        // Sarana & prasarana -> Koordinator Logistik.
        $this->actingAs($this->pelapor)->post(route('complaints.store'), [
            'category_id' => Category::where('koordinator_role', 'koordinator_logistik')->firstOrFail()->id,
            'location_id' => Location::first()->id,
            'title' => 'Bangku kelas rusak parah',
            'description' => 'Dua bangku di ruang kelas 1 patah sehingga santri harus duduk di lantai.',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $sarana = Complaint::latest('id')->firstOrFail();

        $this->assertEquals('ditugaskan', $sarana->status);
        $this->assertEquals($this->logistik->id, $sarana->assigned_to);
        $this->assertEquals(1, $this->logistik->notifications()->count());
        $this->assertEquals(0, $this->keamanan->notifications()->count());

        // Tembusan pengawasan: admin & pimpinan diberi tahu agar bisa memantau.
        $this->assertEquals(1, $this->admin->notifications()->count());
        $this->assertEquals(1, $this->pimpinan->notifications()->count());
        $this->assertEquals(route('admin.monitoring'), $this->admin->notifications()->latest()->first()->data['url']);

        // Keamanan -> Koordinator Keamanan (bukan Logistik).
        $this->actingAs($this->pelapor)->post(route('complaints.store'), [
            'category_id' => Category::where('koordinator_role', 'koordinator_keamanan')->firstOrFail()->id,
            'location_id' => Location::first()->id,
            'title' => 'Perundungan di asrama putra blok B',
            'description' => 'Seorang santri dipukul dan diintimidasi oleh kakak kelas secara berulang.',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $keamanan = Complaint::latest('id')->firstOrFail();

        $this->assertEquals($this->keamanan->id, $keamanan->assigned_to);
        $this->assertEquals(1, $this->keamanan->notifications()->count());
        $this->actingAs($this->keamanan)->get(route('complaints.show', $keamanan))->assertOk();
        // Koordinator divisi lain tidak boleh membaca laporan keamanan (404 = disamarkan).
        $this->actingAs($this->logistik)->get(route('complaints.show', $keamanan))->assertNotFound();
    }

    public function test_laporan_tamu_dikirim_ke_koordinator_dan_admin_pimpinan(): void
    {
        $this->post(route('guest.complaints.store'), [
            'category_id' => Category::where('koordinator_role', 'koordinator_logistik')->firstOrFail()->id,
            'location_id' => Location::first()->id,
            'title' => 'Keran kamar mandi putra bocor',
            'description' => 'Air keran kamar mandi putra terus mengalir sehingga lantai becek seharian.',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $complaint = Complaint::firstOrFail();

        $this->assertEquals('ditugaskan', $complaint->status);
        $this->assertEquals($this->logistik->id, $complaint->assigned_to);
        // Hanya satu koordinator divisi pada seeder ini; jangan sampai tergandakan.
        $assignment = $complaint->assignments()->firstOrFail();
        $this->assertEquals($this->logistik->id, $assignment->assigned_to);
        $this->assertEquals(1, $this->logistik->notifications()->count());
        $this->assertEquals(1, $this->admin->notifications()->count());
        $this->assertEquals(1, $this->pimpinan->notifications()->count());
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
