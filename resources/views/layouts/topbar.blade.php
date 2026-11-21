@php
    $hour = now('Asia/Jakarta')->format('G');
    if ($hour < 4) {
        $timeGreeting = 'Selamat malam';
        $timeEmoji = '🌙';
        $timeMessage = 'Terima kasih sudah tetap berjaga malam ini.';
    } elseif ($hour < 11) {
        $timeGreeting = 'Selamat pagi';
        $timeEmoji = '🌅';
        $timeMessage = 'Semoga hari ini lancar dan penuh keberkahan.';
    } elseif ($hour < 15) {
        $timeGreeting = 'Selamat siang';
        $timeEmoji = '☀️';
        $timeMessage = 'Semoga aktivitas hari ini berjalan dengan baik.';
    } elseif ($hour < 18) {
        $timeGreeting = 'Selamat sore';
        $timeEmoji = '🌤️';
        $timeMessage = 'Semangat menyelesaikan tugas hari ini, ya!';
    } else {
        $timeGreeting = 'Selamat malam';
        $timeEmoji = '🌙';
        $timeMessage = 'Terima kasih atas kerja kerasmu hari ini.';
    }

    // Hanya petugas, admin, pimpinan yang login. Role pelapor sudah tidak ada.
    $roleMessages = [
        'petugas' => 'Terima kasih atas dedikasimu menangani setiap aduan dengan tuntas dan penuh empati.',
        'admin' => 'Sistem siap membantumu mengelola aduan, lokasi, dan pengguna dengan tertib dan transparan.',
        'pimpinan' => 'Ringkasan laporan dan persetujuan hari ini sudah menunggu tinjauan Anda.',
    ];
    $roleMessage = $roleMessages[auth()->user()->role ?? 'petugas'] ?? $roleMessages['petugas'];
@endphp

<header class="sticky top-0 z-20 border-b border-slate-900/[0.06] bg-white/80 backdrop-blur-xl">
    <div class="mx-auto flex w-full max-w-6xl items-center gap-3 px-4 py-3 sm:gap-4 sm:px-6 sm:py-3.5 lg:px-8">

        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2.5">
                <h1 class="truncate text-[17px] font-bold tracking-tight text-slate-900 sm:text-lg">@yield('page-title', 'Dashboard')</h1>
                <span
                    class="hidden shrink-0 rounded-full bg-navy-900/[0.06] px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-[0.1em] text-navy-700 ring-1 ring-navy-900/10 sm:inline-block">{{ auth()->user()->roleLabel() }}</span>
            </div>
            <p class="hidden truncate text-xs text-slate-500 sm:block">@yield('page-subtitle', 'Kelola aduan dan pelaporan dengan tertib dan transparan.')</p>
        </div>

        <a href="{{ route('notifications.index') }}"
            class="lift-soft lift relative rounded-xl border border-slate-200 bg-white p-2.5 text-slate-600 shadow-sm hover:border-navy-200 hover:text-navy-800"
            aria-label="Notifikasi">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 17h5l-1.4-1.4A2 2 0 0118 14.6V11a6 6 0 10-12 0v3.6a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            @php($unread = auth()->user()->unreadNotifications()->count())
            @if ($unread > 0)
                <span
                    data-notif-badge
                    class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white shadow-md shadow-red-600/40 ring-2 ring-white {{ $unread > 0 ? '' : 'hidden' }}">{{ $unread > 9 ? '9+' : $unread }}</span>
            @endif
        </a>

        <div class="relative">
            <button id="user-menu-btn"
                class="btn-press flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-white py-1.5 pl-1.5 pr-2.5 shadow-sm hover:border-navy-200">
                <span
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-navy-800 to-navy-950 text-xs font-bold text-white shadow-inner">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <span class="hidden text-left sm:block">
                    <span
                        class="block max-w-32 truncate text-[13px] font-semibold leading-tight text-slate-800">{{ auth()->user()->name }}</span>
                    <span
                        class="block text-[11px] leading-tight text-slate-500">{{ auth()->user()->roleLabel() }}</span>
                </span>
                <svg class="hidden h-3.5 w-3.5 text-slate-400 sm:block" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div id="user-menu"
                class="absolute right-0 mt-2 hidden w-52 overflow-hidden rounded-2xl border border-slate-200/80 bg-white py-1.5 shadow-[0_24px_48px_-20px_rgba(16,27,45,0.35)]">
                <div class="border-b border-slate-100 px-4 py-2.5">
                    <p class="truncate text-[13px] font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                    <p class="truncate text-[11px] text-slate-500">{{ auth()->user()->email }}</p>
                </div>
                <a href="{{ route('profile.edit') }}"
                    class="block px-4 py-2 text-[13px] font-medium text-slate-700 hover:bg-slate-50">Profil saya</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        class="block w-full px-4 py-2 text-left text-[13px] font-medium text-red-700 hover:bg-red-50">Keluar</button>
                </form>
            </div>
        </div>
    </div>
</header>

<div class="mx-auto w-full max-w-6xl px-4 pt-4 sm:px-6 lg:px-8" role="status" data-greeting-toolbar>
    <div class="flex items-start gap-3 rounded-2xl border border-navy-900/10 bg-gradient-to-r from-white via-white to-brass-500/10 px-4 py-3.5 shadow-sm sm:items-center sm:px-5">
        <span data-greeting="emoji" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-navy-900/[0.06] text-xl" aria-hidden="true">{{ $timeEmoji }}</span>
        <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-800">Assalamu'alaikum, <span data-greeting="text">{{ $timeGreeting }}</span>, {{ auth()->user()->name }}.</p>
            <p class="mt-0.5 text-xs leading-relaxed text-slate-500"><span data-greeting="message">{{ $timeMessage }}</span> {{ $roleMessage }} <span class="hidden font-medium text-navy-700 sm:inline">· <span data-greeting="clock">{{ now('Asia/Jakarta')->translatedFormat('H:i') }}</span> WIB</span></p>
        </div>
    </div>
</div>
