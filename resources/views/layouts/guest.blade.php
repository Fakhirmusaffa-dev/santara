<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SANTARA') — PONPES AL-ITTIHAD</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-santara.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-santara.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="app-shell pondok-shell relative min-h-screen text-slate-800 antialiased">
    {{-- Ilustrasi sebagai latar halaman publik (form lapor & cek status).
         Sengaja TIDAK `fixed`: dengan `fixed inset-0` kotaknya hanya seluas layar,
         sehingga ilustrasi berhenti di tengah halaman. Sebagai lapisan absolut di
         dalam `body` (yang `relative`), tingginya mengikuti tinggi DOKUMEN. --}}
    <div class="pointer-events-none absolute inset-0 -z-10 pondok-backdrop" aria-hidden="true"></div>

    <header class="sticky top-0 z-20 border-b border-slate-900/[0.06] bg-white/80 backdrop-blur-xl">
        <div class="mx-auto flex w-full max-w-3xl items-center justify-between gap-3 px-3 py-2.5 sm:px-6 sm:py-3">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-santara.png') }}" alt="Logo SANTARA"
                    class="h-10 w-10 rounded-xl bg-white object-contain p-1 ring-1 ring-slate-200">
                <div>
                    <p class="text-sm font-bold tracking-[0.14em] text-slate-900">SANTARA</p>
                    <p class="text-[10px] font-medium uppercase tracking-[0.12em] text-slate-500">Ponpes Al-Ittihad</p>
                </div>
            </div>
            <a href="{{ route('login') }}"
                class="btn-press rounded-xl border border-slate-200 bg-white px-4 py-2 text-[13px] font-semibold text-slate-600 shadow-sm hover:border-navy-200 hover:text-navy-800">Masuk
                staf</a>
        </div>
    </header>

    <main class="mx-auto w-full max-w-3xl flex-1 px-2.5 py-3 sm:px-6 sm:py-8">
        @if (session('success'))
            <div data-toast
                class="reveal mb-5 flex items-start gap-3 rounded-2xl border border-emerald-900/10 bg-white/95 px-4 py-3.5 text-sm text-emerald-900 shadow-[0_16px_40px_-20px_rgba(16,90,60,0.45)] ring-1 ring-emerald-900/5 backdrop-blur">
                <span
                    class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
                <p class="pt-1 font-medium">{{ session('success') }}</p>
            </div>
        @endif
        @if (session('guest_ticket'))
            <div
                class="reveal mb-5 rounded-2xl border border-navy-900/10 bg-gradient-to-r from-white to-brass-500/10 px-4 py-4 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Nomor tiket laporan Anda
                </p>
                <div class="mt-1 flex flex-wrap items-center gap-3">
                    <p class="text-lg font-bold tracking-wide text-navy-900">{{ session('guest_ticket') }}</p>
                    <a href="{{ route('guest.complaints.track', ['ticket' => session('guest_ticket')]) }}"
                        class="text-[13px] font-semibold text-navy-700 underline decoration-brass-500/60 underline-offset-4 hover:text-navy-900">Cek
                        status laporan</a>
                </div>
                <p class="mt-1.5 text-xs leading-relaxed text-slate-500">Simpan nomor tiket ini. Anda bisa memakainya
                    untuk
                    memantau perkembangan laporan kapan saja tanpa perlu akun.</p>
            </div>
        @endif
        @if ($errors->any())
            <div
                class="reveal mb-5 rounded-2xl border border-red-900/10 bg-white/95 px-4 py-3.5 text-sm text-red-900 shadow-sm ring-1 ring-red-900/5 backdrop-blur">
                <p class="font-semibold">Periksa kembali isian berikut:</p>
                <ul class="mt-1.5 list-disc space-y-0.5 pl-5 text-[13px]">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer
        class="border-t border-slate-900/[0.06] bg-white/70 px-6 py-4 text-center text-xs text-slate-500 backdrop-blur">
        <span class="font-semibold tracking-wide text-slate-700">SANTARA</span>
        <span class="mx-1.5 text-slate-300">·</span> Sistem Aduan dan Pelaporan Terpadu
        <span class="mx-1.5 text-slate-300">·</span> PONPES AL-ITTIHAD
    </footer>

    <x-loader />
</body>

</html>
