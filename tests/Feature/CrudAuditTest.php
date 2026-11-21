<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrudAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_crud_pages_render(): void
    {
        $this->withoutVite();
        $this->seed(\Database\Seeders\SantaraSeeder::class);
        $admin = User::where('email', 'admin@santara.test')->firstOrFail();
        $cat = Category::firstOrFail();
        $loc = Location::firstOrFail();
        $target = User::where('email', 'pelapor@santara.test')->firstOrFail();

        $pages = [
            route('admin.locations.index'),
            route('admin.locations.show', $loc),
            route('admin.locations.edit', $loc),
            route('admin.categories.index'),
            route('admin.categories.show', $cat),
            route('admin.categories.edit', $cat),
            route('admin.users.index'),
            route('admin.users.create'),
            route('admin.users.show', $target),
            route('admin.users.edit', $target),
        ];

        foreach ($pages as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_admin_can_update_and_delete_master_data(): void
    {
        $this->withoutVite();
        $this->seed(\Database\Seeders\SantaraSeeder::class);
        $admin = User::where('email', 'admin@santara.test')->firstOrFail();
        $cat = Category::firstOrFail();
        $loc = Location::firstOrFail();

        // Update kategori
        $this->actingAs($admin)->put(route('admin.categories.update', $cat), [
            'name' => $cat->name . ' Revisi',
            'description' => 'Uji ubah.',
            'is_active' => '1',
        ])->assertRedirect(route('admin.categories.show', $cat));
        $this->assertDatabaseHas('categories', ['id' => $cat->id, 'name' => $cat->name . ' Revisi']);

        // Update lokasi
        $this->actingAs($admin)->put(route('admin.locations.update', $loc), [
            'name' => $loc->name . ' Revisi',
            'code' => $loc->code,
            'area' => $loc->area,
            'description' => 'Uji ubah.',
            'is_active' => '1',
        ])->assertRedirect(route('admin.locations.show', $loc));
        $this->assertDatabaseHas('locations', ['id' => $loc->id, 'name' => $loc->name . ' Revisi']);

        // Hapus kategori & lokasi yang tidak dipakai
        $baru = Category::create(['name' => 'Kategori Buang', 'slug' => 'kategori-buang']);
        $this->actingAs($admin)->delete(route('admin.categories.destroy', $baru))->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $baru->id]);

        $lokasiBaru = Location::create(['name' => 'Lokasi Buang', 'code' => 'BUANG-01', 'area' => 'umum']);
        $this->actingAs($admin)->delete(route('admin.locations.destroy', $lokasiBaru))->assertRedirect(route('admin.locations.index'));
        $this->assertDatabaseMissing('locations', ['id' => $lokasiBaru->id]);

        // Yang dipakai laporan: hapus harus ditolak (bukan 500)
        $this->actingAs($admin)->post(route('complaints.store'), [
            'category_id' => $cat->id,
            'location_id' => $loc->id,
            'title' => 'Lampu kelas mati total perlu perbaikan',
            'description' => 'Lampu ruang kelas 1 mati total sejak kemarin sore sehingga KBM terganggu.',
            'priority' => 'sedang',
            'sensitivity' => 'biasa',
        ])->assertRedirect();
        $this->actingAs($admin)->delete(route('admin.categories.destroy', $cat))->assertSessionHasErrors('category');
        $this->actingAs($admin)->delete(route('admin.locations.destroy', $loc))->assertSessionHasErrors('location');
    }

    public function test_admin_can_update_and_delete_user_and_complaint(): void
    {
        $this->withoutVite();
        $this->seed(\Database\Seeders\SantaraSeeder::class);
        $admin = User::where('email', 'admin@santara.test')->firstOrFail();
        $pelapor = User::where('email', 'pelapor@santara.test')->firstOrFail();

        // Update user
        $this->actingAs($admin)->put(route('admin.users.update', $pelapor), [
            'name' => 'Ahmad Revisi',
            'email' => 'pelapor@santara.test',
            'role' => 'petugas',
            'position' => 'Petugas',
            'phone' => '0812',
            'is_active' => '1',
        ])->assertRedirect(route('admin.users.show', $pelapor));
        $this->assertDatabaseHas('users', ['id' => $pelapor->id, 'name' => 'Ahmad Revisi', 'role' => 'petugas']);

        // Hapus user yang tidak dipakai
        $kosong = User::create(['name' => 'Buang', 'email' => 'buang@santara.test', 'password' => 'password123', 'role' => 'pelapor', 'is_active' => true]);
        $this->actingAs($admin)->delete(route('admin.users.destroy', $kosong))->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $kosong->id]);

        // Hapus laporan sebagai admin
        $this->actingAs($pelapor)->post(route('complaints.store'), [
            'category_id' => Category::first()->id,
            'location_id' => Location::first()->id,
            'title' => 'Keran asrama bocor parah perlu teknisi',
            'description' => 'Keran wastafel asrama putra blok A bocor sejak dua hari dan menggenangi lantai.',
            'priority' => 'tinggi',
            'sensitivity' => 'biasa',
        ])->assertRedirect();
        $complaint = \App\Models\Complaint::latest()->firstOrFail();
        $this->actingAs($admin)->delete(route('complaints.destroy', $complaint))->assertRedirect(route('complaints.index'));
        $this->assertSoftDeleted('complaints', ['id' => $complaint->id]);
    }
}
