<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\BroadcastingNotification;
use App\Notifications\GenericAnnouncement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiagnosticNotifyChannelTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_channel_direct(): void
    {
        $this->seed(\Database\Seeders\SantaraSeeder::class);
        $admin = User::where('email', 'admin@santara.test')->firstOrFail();

        $inner = new GenericAnnouncement('T', 'M');
        $inner->id = (string) \Illuminate\Support\Str::uuid();

        try {
            $row = app(\Illuminate\Notifications\ChannelManager::class)->driver('database')->send($admin, $inner);
            $this->log('DIRECT ok row=' . var_export((bool) $row, true) . ' count=' . \DB::table('notifications')->count());
        } catch (\Throwable $e) {
            $this->log('DIRECT FAIL ' . get_class($e) . ': ' . $e->getMessage());
        }

        $psm = new BroadcastingNotification($inner, $admin);
        $psm->id = (string) \Illuminate\Support\Str::uuid();

        try {
            $row = app(\Illuminate\Notifications\ChannelManager::class)->driver('database')->send($admin, $psm);
            $this->log('WRAPPER ok row=' . var_export((bool) $row, true) . ' count=' . \DB::table('notifications')->count());
        } catch (\Throwable $e) {
            $this->log('WRAPPER FAIL ' . get_class($e) . ': ' . $e->getMessage());
        }

        $this->log('FINAL count=' . \DB::table('notifications')->count());
        $this->assertTrue(true);
    }

    private function log(string $line): void
    {
        file_put_contents(storage_path('logs/diagnostic.txt'), $line . PHP_EOL, FILE_APPEND);
    }
}
