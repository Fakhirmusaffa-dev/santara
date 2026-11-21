<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — SANTARA PONPES AL-ITTIHAD</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-santara.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-santara.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="ios-type relative min-h-screen bg-navy-950 text-slate-800 antialiased">
    <div class="fixed inset-0 -z-10 auth-backdrop bg-cover bg-center"></div>
    <div class="fixed inset-0 -z-10 bg-navy-950/55"></div>
    <div class="fixed inset-0 -z-10 [background-image:radial-gradient(1000px_480px_at_110%_-10%,rgba(185,151,91,0.16),transparent_60%),radial-gradient(900px_520px_at_-10%_110%,rgba(76,100,137,0.28),transparent_60%)]"></div>
    <div class="mx-auto flex min-h-screen w-full max-w-md flex-col items-center justify-center px-4 py-10 sm:px-6">
        <div class="reveal mx-auto w-full max-w-md delay-160">
            <div
                class="login-card lift relative overflow-hidden rounded-[28px]">
                {{-- Kilau lembut di sudut atas kartu agar kesan liquid glass lebih hidup. --}}
                <div
                    class="pointer-events-none absolute inset-0 [background-image:radial-gradient(520px_260px_at_15%_0%,rgba(255,255,255,0.55),transparent_65%)]">
                </div>
                <div class="relative h-1.5 bg-gradient-to-r from-navy-900 via-brass-500 to-navy-900"></div>
                <div class="relative px-7 pb-7 pt-7 sm:px-9 sm:pb-9 sm:pt-9">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo-ponpes.png') }}" alt="Logo Ponpes Al-Ittihad"
                            class="h-11 w-11 object-contain">
                        <div>
                            <p class="text-sm font-bold tracking-[0.14em]">SANTARA</p>
                            <p class="text-[11px] text-slate-500">PONPES AL-ITTIHAD</p>
                        </div>
                    </div>
                    <h2 class="mt-5 text-[22px] font-bold tracking-tight text-slate-900">Selamat datang kembali
                    </h2>
                    <p class="mt-1.5 text-sm text-slate-600">Masuk untuk mengelola laporan dan tindak lanjut Anda.</p>
                    <a href="{{ route('home') }}"
                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-white/75 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-white/70 backdrop-blur transition hover:bg-white hover:text-navy-900">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6M9 12h12" />
                        </svg>
                        Kembali ke tampilan utama
                    </a>

                    @if ($errors->any())
                        <div class="mt-5 rounded-2xl border border-red-200 bg-red-50/80 px-4 py-3 text-sm text-red-800">
                            @foreach ($errors->all() as $e)
                                <p>{{ $e }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}" data-loading="Memverifikasi akun staf…"
                        class="mt-7 space-y-4">
                        @csrf
                        <div>
                            <label for="email" class="text-[13px] font-semibold text-slate-800">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                                autofocus autocomplete="username"
                                class="mt-1.5 w-full rounded-2xl bg-white/95 px-4 py-3 text-sm text-slate-900 shadow-sm ring-1 ring-white/80 backdrop-blur placeholder:text-slate-400 focus:bg-white"
                                placeholder="nama@alittihad.sch.id">
                        </div>
                        <div>
                            <div class="flex items-center justify-between">
                                <label for="password" class="text-[13px] font-semibold text-slate-800">Kata
                                    sandi</label>
                            </div>
                            <input id="password" type="password" name="password" required
                                autocomplete="current-password"
                                class="mt-1.5 w-full rounded-2xl bg-white/95 px-4 py-3 text-sm text-slate-900 shadow-sm ring-1 ring-white/80 backdrop-blur placeholder:text-slate-400 focus:bg-white"
                                placeholder="••••••••">
                        </div>
                        <label class="flex cursor-pointer items-center gap-2.5 text-sm font-semibold text-slate-800">
                            <input type="checkbox" name="remember" value="1"
                                class="h-4 w-4 rounded accent-navy-900 ring-1 ring-slate-400">
                            Ingat saya di perangkat ini
                        </label>
                        <button type="submit"
                            class="btn-press w-full rounded-2xl bg-navy-900 py-3 text-sm font-semibold text-white shadow-[0_16px_32px_-14px_rgba(20,32,56,0.7)] hover:bg-navy-800">Masuk
                            ke SANTARA</button>
                    </form>

                    <div
                        class="mt-6 rounded-2xl bg-white/70 p-4 text-xs leading-relaxed text-slate-700 shadow-sm ring-1 ring-white/60 backdrop-blur">
                        <p class="font-semibold text-slate-800">Akun staf</p>
                        <p class="mt-1 text-[11px]">Akun diberikan oleh admin pondok. Lupa kata sandi? Hubungi
                            administrator SANTARA.</p>
                    </div>
                </div>
            </div>
            <p class="mt-5 text-center text-[11px] tracking-wide text-slate-300">SANTARA · Sistem Aduan dan Pelaporan
                Terpadu · PONPES AL-ITTIHAD</p>
        </div>
    </div>

    <x-loader />
</body>

</html>
