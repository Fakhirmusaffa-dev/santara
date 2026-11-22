<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\SantaraSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SensitiveTriageTest extends TestCase
{
    use RefreshDatabase;

    public function test_triase_buta_sampai_selesai_tanpa_bocor(): void
    {
        $this->withoutVite();
        $this->seed(SantaraSeeder::class);

        $pelapor = User::where('email', 'pelapor@santara.test')->firstOrFail();
        $admin = User::where('email', 'admin@santara.test')->firstOrFail();
        $pimpinan = User::where('email', 'pimpinan@santara.test')->firstOrFail();
        $petugas = User::where('email', 'petugas@santara.test')->firstOrFail();

        // 1. Santri lapor sensitif. Klasifikasi TIDAK dipilih pelapor: sistem yang
        //    menetapkan karena isinya menyangkut kekerasan (kata kunci berisiko).
        $this->actingAs($pelapor)->post(route('complaints.store'), [
            'category_id' => Category::first()->id,
            'location_id' => Location::first()->id,
            'title' => 'Judul rahasia yang tidak boleh bocor',
            'description' => 'Isi sangat rahasia tentang ancaman kekerasan yang tidak boleh tampil di antrean mana pun.',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $complaint = Complaint::latest()->firstOrFail();

        $this->assertSame('sensitif', $complaint->sensitivity);

        // 2. Admin lain dapat notifikasi tiket SAJA (tanpa isi laporan).
        $notif = $admin->notifications()->latest()->firstOrFail();
        $this->assertStringContainsString($complaint->ticket_number, $notif->data['message']);
        $this->assertStringNotContainsString('rahasia', strtolower($notif->data['message']));
        $this->assertEquals(route('admin.sensitive.index'), $notif->data['url']);

        // 3. Laporan sensitif TIDAK muncul di daftar umum siapa pun
        // (tabel daftar; toast sukses boleh menyebut tiket — itu milik sesi sendiri).
        foreach ([$admin, $pimpinan, $petugas] as $u) {
            $page = $this->actingAs($u)->get(route('complaints.index'))->assertOk();
            $page->assertDontSee('Judul rahasia');
            $page->assertDontSee('Isi sangat rahasia');
            $page->assertSee('Tidak ada laporan ditemukan');
        }

        // 4. Halaman triase: metadata tampil, isi TIDAK tampil.
        $triase = $this->actingAs($admin)->get(route('admin.sensitive.index'))->assertOk();
        $triase->assertSee($complaint->ticket_number);
        $triase->assertSee('Belum ditugaskan');
        $triase->assertDontSee('Judul rahasia');
        $triase->assertDontSee('Isi sangat rahasia');
        $triase->assertDontSee($pelapor->name);
        $triase->assertDontSee($pelapor->email);

        // Pimpinan tidak boleh buka halaman triase (khusus admin).
        $this->actingAs($pimpinan)->get(route('admin.sensitive.index'))->assertForbidden();

        // 5. Sebelum ambil alih: verify/assign/edit/hapus sensitif wajib 403.
        $this->actingAs($admin)->post(route('complaints.verify', $complaint))->assertForbidden();
        $this->actingAs($admin)->post(route('complaints.assign', $complaint), [
            'assigned_to' => $petugas->id,
            'instruction' => 'Instruksi yang cukup panjang untuk lolos validasi.',
        ])->assertForbidden();
        $this->actingAs($admin)->get(route('complaints.show', $complaint))->assertForbidden();

        // 6. Admin ambil alih dari triase -> tercatat, lalu bisa buka isi.
        $this->actingAs($admin)->post(route('complaints.grant-access', $complaint), [
            'user_id' => $admin->id,
            'reason' => 'Triase buta: mengambil alih penanganan awal.',
        ])->assertRedirect();
        $this->assertDatabaseHas('complaint_accesses', ['complaint_id' => $complaint->id, 'user_id' => $admin->id]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'sensitive_access_granted']);
        $this->actingAs($admin)->get(route('complaints.show', $complaint))->assertOk();

        // 7. Admin tunjuk pimpinan -> pimpinan bisa buka isi.
        $this->actingAs($admin)->post(route('complaints.grant-access', $complaint), [
            'user_id' => $pimpinan->id,
            'reason' => 'Evaluasi pimpinan atas kasus sensitif.',
        ])->assertRedirect();
        $this->actingAs($pimpinan)->get(route('complaints.show', $complaint))->assertOk();

        // 8. Alur normal berlanjut: verifikasi -> tugaskan -> petugas bisa kerja.
        $this->actingAs($admin)->post(route('complaints.verify', $complaint), ['verification_note' => 'Valid.'])->assertRedirect();
        $this->actingAs($admin)->post(route('complaints.assign', $complaint), [
            'assigned_to' => $petugas->id,
            'instruction' => 'Tangani dengan hati-hati dan jaga kerahasiaan.',
        ])->assertRedirect();
        $this->assertEquals('ditugaskan', $complaint->fresh()->status);
        $this->actingAs($petugas)->get(route('complaints.show', $complaint))->assertOk();
    }
}
