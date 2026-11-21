<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if (auth()->check())
        <meta name="user-id" content="{{ auth()->id() }}">
    @endif
    <title>@yield('title', 'SANTARA') — PONPES AL-ITTIHAD</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-santara.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-santara.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="app-shell min-h-screen text-slate-800 antialiased">
    <div class="flex min-h-screen">
        @include('layouts.sidebar')

        <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
            @include('layouts.topbar')

            <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                @if (session('success'))
                    <div data-toast
                        class="reveal mb-5 flex items-start gap-3 rounded-2xl border border-emerald-900/10 bg-white/95 px-4 py-3.5 text-sm text-emerald-900 shadow-[0_16px_40px_-20px_rgba(16,90,60,0.45)] ring-1 ring-emerald-900/5 backdrop-blur transition-opacity">
                        <span
                            class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <p class="pt-1 font-medium">{{ session('success') }}</p>
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

            <div class="h-24 lg:hidden"></div>

            <footer
                class="border-t border-slate-900/[0.06] bg-white/70 px-6 py-4 text-center text-xs text-slate-500 backdrop-blur">
                <span class="font-semibold tracking-wide text-slate-700">SANTARA</span>
                <span class="mx-1.5 text-slate-300">·</span> Sistem Aduan dan Pelaporan Terpadu
                <span class="mx-1.5 text-slate-300">·</span> PONPES AL-ITTIHAD
            </footer>
        </div>
    </div>
    @include('layouts.tabbar')

    <x-loader />
</body>

</html>
