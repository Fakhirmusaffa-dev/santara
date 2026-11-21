<?php

namespace App\Providers;

use App\Models\Complaint;
use App\Policies\ComplaintPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Daftarkan policy agar $user->can('viewSensitive', $complaint) & authorize()
        // resmi mengarah ke ComplaintPolicy — termasuk untuk lampiran sensitif.
        Gate::policy(Complaint::class, ComplaintPolicy::class);
        RateLimiter::for('login', function (Request $request) {
            $key = Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip());

            return Limit::perMinute(5)->by($key);
        });
        Gate::define('view-sensitive', [ComplaintPolicy::class, 'viewSensitive']);
        Gate::define('viewSensitive', [ComplaintPolicy::class, 'viewSensitive']);
    }
}
