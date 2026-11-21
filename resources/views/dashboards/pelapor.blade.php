@extends('layouts.app')
@section('title', 'Dashboard Pelapor')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Pantau laporan yang Anda sampaikan.')

@section('content')
    <div class="reveal grid grid-cols-2 gap-4 delay-40 lg:grid-cols-4">
        <x-stat title="Total Laporan" :value="$total" accent="navy"
            icon="M9 12h6m-6 4h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
        <x-stat title="Menunggu Verifikasi" :value="$menunggu" hint="Antrean admin" accent="gold"
            icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat title="Sedang Ditangani" :value="$ditangani" hint="Ditugaskan & progres" accent="slate"
            icon="M13 10V3L4 14h7v7l9-11h-7z" />
        <x-stat title="Selesai" :value="$selesai" hint="Siap dinilai" accent="navy"
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </div>

    <div class="reveal mt-6 delay-120">
        <a href="{{ route('complaints.create') }}"
            class="lift card-shadow group relative flex items-center justify-between gap-4 overflow-hidden rounded-2xl bg-navy-950 px-5 py-5 text-white sm:px-6">
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(520px_200px_at_110%_-40px,rgba(221,199,154,0.22),transparent_65%),radial-gradient(480px_220px_at_-10%_120%,rgba(76,100,137,0.4),transparent_65%)]">
            </div>
            <span class="relative">
                <span class="block text-[15px] font-semibold tracking-tight">Ada kerusakan atau kejadian baru?</span>
                <span class="mt-1 block text-xs leading-relaxed text-slate-300">Buat laporan — cukup 1 menit, lengkapi
                    foto bila ada.</span>
            </span>
            <span
                class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-xl font-semibold ring-1 ring-white/20 transition-transform duration-300 group-hover:scale-110 group-hover:bg-brass-500 group-hover:text-navy-950">+</span>
        </a>
    </div>

    <div class="reveal mt-6 grid gap-4 delay-200 lg:grid-cols-3">
        <x-card title="Laporan terbaru" class="lg:col-span-2">
            <div class="-mx-1">
                @forelse($terbaru as $c)
                    <a href="{{ route('complaints.show', $c) }}"
                        class="row-hover group flex items-center justify-between gap-3 rounded-xl px-3 py-3 hover:bg-slate-50">
                        <span class="min-w-0">
                            <span
                                class="block truncate text-sm font-semibold text-slate-800 group-hover:text-navy-800">{{ $c->title }}</span>
                            <span class="mt-0.5 block text-xs text-slate-500">{{ $c->ticket_number }} ·
                                {{ $c->category?->name }} · {{ $c->created_at->diffForHumans() }}</span>
                        </span>
                        <x-status :status="$c->status" />
                    </a>
                @empty
                    <x-empty-state title="Belum ada laporan" hint="Laporan yang Anda buat akan tampil di sini." />
                @endforelse
            </div>
            <div class="mt-3 border-t border-slate-100 pt-3"><a href="{{ route('complaints.index') }}"
                    class="link-arrow text-sm font-semibold text-navy-700 hover:text-navy-900">Lihat semua laporan
                    <span class="arrow inline-block">→</span></a></div>
        </x-card>

        <x-card title="Notifikasi">
            @forelse($notifikasi as $n)
                <div class="rounded-xl px-1 py-2.5 text-sm transition hover:bg-slate-50">
                    <p class="font-medium text-slate-800">{{ $n->data['title'] ?? 'Notifikasi' }}</p>
                    <p class="mt-0.5 line-clamp-2 text-xs leading-relaxed text-slate-500">{{ $n->data['message'] ?? '' }}
                    </p>
                </div>
            @empty
                <p class="text-sm text-slate-500">Tidak ada notifikasi baru.</p>
            @endforelse
        </x-card>
    </div>
@endsection
