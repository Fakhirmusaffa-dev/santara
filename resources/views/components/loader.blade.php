{{--
    Overlay loading berlogo Ponpes Al-Ittihad.
    Diaktifkan otomatis oleh app.js saat <form data-loading> dikirim atau <a data-loading> diklik.
    Bisa juga dipicu manual: window.SantaraLoader.show('Memproses…').
--}}
<div id="santara-loader" class="santara-loader" role="status" aria-live="polite" aria-busy="true" hidden>
    <div class="santara-loader-card">
        <span class="santara-loader-ring" aria-hidden="true">
            <img src="{{ asset('images/logo-santara.png') }}" alt="" class="santara-loader-logo">
        </span>
        <p class="santara-loader-title">SANTARA</p>
        <p id="santara-loader-text" class="santara-loader-text">Memuat…</p>
        <div class="santara-loader-bar" aria-hidden="true"><span></span></div>
    </div>
    <span class="sr-only">Sedang memuat, mohon tunggu.</span>
</div>
