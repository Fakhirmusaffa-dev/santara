@php
    $role = auth()->user()->role ?? 'petugas';
    $home = auth()->user()?->dashboardRoute() ?? 'petugas.dashboard';
    $allMenus = [
        'petugas' => [
            ['route' => $home, 'label' => 'Beranda', 'icon' => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
            [
                'route' => 'complaints.index',
                'label' => 'Laporan',
                'icon' => 'M9 12h6m-6 4h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z',
            ],
            ['route' => 'complaints.create', 'label' => 'Buat', 'icon' => 'M12 4v16m8-8H4'],
        ],
        'admin' => [
            ['route' => $home, 'label' => 'Beranda', 'icon' => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
            [
                'route' => 'complaints.index',
                'label' => 'Laporan',
                'icon' => 'M9 12h6m-6 4h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z',
            ],
            [
                'route' => 'admin.locations.index',
                'label' => 'Lokasi',
                'icon' =>
                    'M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.242a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z',
            ],
            [
                'route' => 'admin.categories.index',
                'label' => 'Kategori',
                'icon' =>
                    'M7 7h.01M7 3h5a2 2 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z',
            ],
            [
                'route' => 'admin.users.index',
                'label' => 'Pengguna',
                'icon' =>
                    'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6-4a3 3 0 11-3-3',
            ],
        ],
        'pimpinan' => [
            ['route' => $home, 'label' => 'Beranda', 'icon' => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
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
        ],
    ];
    // Ambil maksimal 4 menu utama; sisanya tetap bisa diakses lewat drawer "Menu".
    $tabs = array_slice($allMenus[$role] ?? $allMenus['petugas'], 0, 4);
@endphp

<div class="glass-tabbar lg:hidden" role="navigation" aria-label="Navigasi utama">
    <nav>
        @foreach ($tabs as $tab)
            <a href="{{ route($tab['route']) }}"
                class="tab-item {{ request()->routeIs($tab['route']) ? 'is-active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tab['icon'] }}" />
                </svg>
                <span class="max-w-full truncate">{{ $tab['label'] }}</span>
            </a>
        @endforeach
        <button type="button" id="tabbar-menu" class="tab-item" aria-controls="mobile-drawer" aria-expanded="false">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <span>Menu</span>
        </button>
    </nav>
</div>
