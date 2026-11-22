@php
    $role = auth()->user()->role ?? 'koordinator_logistik';
    $sectionOf = [
        'koordinator_keamanan' => 'Divisi Keamanan',
        'koordinator_logistik' => 'Divisi Logistik',
        'admin' => 'Administrasi',
        'pimpinan' => 'Pengawasan',
    ];
    $koordinatorMenu = [
        [
            'route' => 'koordinator.dashboard',
            'label' => 'Tugas Saya',
            'icon' =>
                'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
        ],
        [
            'route' => 'complaints.index',
            'label' => 'Laporan Divisi',
            'icon' => 'M9 12h6m-6 4h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z',
        ],
        [
            'route' => 'notifications.index',
            'label' => 'Notifikasi',
            'icon' =>
                'M15 17h5l-1.4-1.4A2 2 0 0118 14.6V11a6 6 0 10-12 0v3.6a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
        ],
    ];
    $menus = [
        'koordinator_keamanan' => $koordinatorMenu,
        'koordinator_logistik' => $koordinatorMenu,
        'admin' => [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
            [
                'route' => 'complaints.index',
                'label' => 'Semua Laporan',
                'icon' => 'M9 12h6m-6 4h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z',
            ],
            ['route' => 'admin.monitoring', 'label' => 'Monitoring', 'icon' => 'M3 3v18h18M7 14l4-4 3 3 5-6'],
            [
                'route' => 'admin.sensitive.index',
                'label' => 'Sensitif Masuk',
                'icon' =>
                    'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v2',
            ],
            [
                'route' => 'approvals.index',
                'label' => 'Persetujuan',
                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            [
                'route' => 'reports.index',
                'label' => 'Laporan',
                'icon' => 'M7 3h7l5 5v13a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z',
            ],
            [
                'route' => 'admin.users.index',
                'label' => 'Pengguna',
                'icon' =>
                    'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6-4a3 3 0 11-3-3',
            ],
            ['route' => 'admin.categories.index', 'label' => 'Kategori', 'icon' => 'M7 7h10M7 12h10M7 17h10'],
            [
                'route' => 'admin.locations.index',
                'label' => 'Lokasi',
                'icon' => 'M17.7 13.9a8 8 0 10-11.4 0L12 21l5.7-7.1zM12 12a2 2 0 100-4 2 2 0 000 4z',
            ],
            [
                'route' => 'admin.activities',
                'label' => 'Audit Log',
                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            [
                'route' => 'feedbacks.index',
                'label' => 'Feedback',
                'icon' => 'M11.5 2l8.5 8.5-8.5 8.5-1.4-1.4 7.1-7.1-7.1-7.1L11.5 2zM4 11h9v2H4z',
            ],
        ],
        'pimpinan' => [
            ['route' => 'pimpinan.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
            [
                'route' => 'complaints.index',
                'label' => 'Laporan',
                'icon' => 'M9 12h6m-6 4h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z',
            ],
            [
                'route' => 'approvals.index',
                'label' => 'Persetujuan',
                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            ['route' => 'reports.index', 'label' => 'Evaluasi', 'icon' => 'M3 3v18h18M7 14l4-4 3 3 5-6'],
            [
                'route' => 'feedbacks.index',
                'label' => 'Feedback',
                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
        ],
    ];
    $items = $menus[$role] ?? $menus['admin'];
    $link = function ($r, $extra = '') use ($items) {
        $active = request()->routeIs($r)
            ? 'is-active bg-white/[0.09] text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.08)] ring-1 ring-white/10'
            : 'text-slate-400 hover:bg-white/[0.06] hover:text-white';
        return "nav-item flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-[13px] font-medium $active $extra";
    };
@endphp

{{-- Desktop sidebar --}}
<aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col bg-navy-950 lg:flex">
    <div
        class="pointer-events-none absolute inset-0 bg-[radial-gradient(420px_220px_at_20%_-40px,rgba(221,199,154,0.14),transparent_70%),radial-gradient(520px_300px_at_100%_110%,rgba(76,100,137,0.22),transparent_70%)]">
    </div>
    <div class="relative flex items-center gap-3 border-b border-white/[0.07] px-5 py-5">
        <img src="{{ asset('images/logo-santara.png') }}" alt="Logo SANTARA"
            class="h-11 w-11 rounded-2xl bg-white object-contain p-1 shadow-lg shadow-black/30 ring-1 ring-white/20">
        <div>
            <p class="text-sm font-bold tracking-[0.14em] text-white">SANTARA</p>
            <p class="mt-0.5 text-[10px] font-medium uppercase tracking-[0.12em] text-brass-300/90">Ponpes Al-Ittihad</p>
        </div>
    </div>
    <p class="relative px-5 pb-1 pt-4 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500">
        {{ $sectionOf[$role] ?? 'Menu' }}</p>
    <nav class="relative flex-1 space-y-1 overflow-y-auto px-3 pb-4">
        @foreach ($items as $m)
            <a href="{{ route($m['route']) }}" class="{{ $link($m['route']) }}">
                <svg class="h-[18px] w-[18px] shrink-0 opacity-90" fill="none" stroke="currentColor"
                    stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $m['icon'] }}" />
                </svg>
                {{ $m['label'] }}
            </a>
        @endforeach
        <a href="{{ route('profile.edit') }}" class="{{ $link('profile.edit') }}">
            <svg class="h-[18px] w-[18px] shrink-0 opacity-90" fill="none" stroke="currentColor" stroke-width="1.8"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Profil
        </a>
    </nav>
    <div class="relative border-t border-white/[0.07] p-4">
        <div class="rounded-2xl bg-white/[0.05] p-3.5 ring-1 ring-white/10">
            <p class="text-[11px] font-semibold text-white">Amanah &amp; tertib tindak lanjut</p>
            <p class="mt-1 text-[11px] leading-relaxed text-slate-400">Setiap laporan tercatat, berstatus jelas, dan
                terdokumentasi.</p>
        </div>
    </div>
</aside>

{{-- Mobile drawer --}}
<div id="drawer-backdrop" class="fixed inset-0 z-30 hidden bg-navy-950/60 backdrop-blur-sm lg:hidden"></div>
<aside id="mobile-drawer"
    class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col bg-navy-950 shadow-2xl transition-transform duration-300 ease-out lg:hidden">
    <div class="flex items-center justify-between border-b border-white/[0.07] px-5 py-5">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/logo-santara.png') }}" alt="Logo SANTARA"
                class="h-10 w-10 rounded-xl bg-white object-contain p-1 ring-1 ring-white/20">
            <div>
                <p class="text-sm font-bold tracking-[0.14em] text-white">SANTARA</p>
                <p class="text-[10px] font-medium uppercase tracking-[0.12em] text-brass-300/90">Ponpes Al-Ittihad</p>
            </div>
        </div>
        <button id="drawer-close" class="btn-press rounded-xl p-2 text-slate-300 hover:bg-white/10"
            aria-label="Tutup menu">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        @foreach ($items as $m)
            <a href="{{ route($m['route']) }}" class="{{ $link($m['route']) }}">
                {{ $m['label'] }}
            </a>
        @endforeach
        <a href="{{ route('profile.edit') }}" class="{{ $link('profile.edit') }}">Profil</a>
        <form method="POST" action="{{ route('logout') }}" class="pt-2">
            @csrf
            <button
                class="btn-press w-full rounded-xl bg-white/10 px-3.5 py-2.5 text-left text-sm font-medium text-white ring-1 ring-white/10">Keluar</button>
        </form>
    </nav>
</aside>
