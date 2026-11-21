<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Str::random(40);
        Vite::useCspNonce($nonce);
        // Jangan menambah atribut nonce manual: Laravel sudah menaruh nonce pada <script type="module">.
        // Menimpanya lewat useScriptTagAttributes membuat modul tidak dieksekusi browser.

        $styleSources = "'self'";
        $viteOrigin = $this->viteOrigin();
        $assetSources = "'self'";

        if ($viteOrigin !== null) {
            $styleSources .= " {$viteOrigin}";
            $assetSources .= " {$viteOrigin}";
        }

        if (! config('app.drop_style_attributes')) {
            $styleSources .= " 'unsafe-inline'";
        }

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Content-Security-Policy', "default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; img-src {$assetSources} data:; font-src 'self' https://fonts.gstatic.com; style-src {$styleSources} https://fonts.googleapis.com; script-src 'self' 'nonce-{$nonce}'; connect-src 'self' ws: wss:");

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * Origin Vite dev server saat `npm run dev` aktif, agar asetnya tidak diblokir CSP 'self'.
     */
    private function viteOrigin(): ?string
    {
        if (! Vite::isRunningHot()) {
            return null;
        }

        return rtrim(strtok((string) @file_get_contents(public_path('hot')), "\n"), '/') ?: null;
    }
}
