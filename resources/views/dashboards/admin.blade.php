@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Admin')
@section('page-subtitle', 'Kelola laporan, penugasan, dan master data.')

@section('content')
    <div class="reveal grid grid-cols-2 gap-4 delay-40 lg:grid-cols-4">
        <x-stat title="Total Laporan" :value="$total" hint="Non-sensitif" accent="navy"
            icon="M9 12h6m-6 4h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
        <x-stat title="Menunggu Verifikasi" :value="$byStatus['menunggu_verifikasi'] ?? 0" hint="Antrean masuk" accent="gold"
            icon="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat title="Sedang Ditangani" :value="($byStatus['ditugaskan'] ?? 0) +
            ($byStatus['sedang_ditangani'] ?? 0) +
            ($byStatus['menunggu_pemeriksaan'] ?? 0)" hint="Aktif diproses" accent="slate"
            icon="M13 10V3L4 14h7v7l9-11h-7z" />
        <x-stat title="Selesai" :value="$byStatus['selesai'] ?? 0" hint="Rata feedback: {{ $rataFeedback }}/5" accent="navy"
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </div>

    <div class="reveal mt-6 grid gap-4 delay-120 lg:grid-cols-3">
        <x-card title="Laporan terbaru" class="lg:col-span-2" :action="route('complaints.index') ? null : null">
            <div class="-mx-1">
                @forelse($terbaru as $c)
                    <a href="{{ route('complaints.show', $c) }}"
                        class="row-hover group flex items-center justify-between gap-3 rounded-xl px-3 py-3 hover:bg-slate-50">
                        <span class="min-w-0">
                            <span
                                class="block truncate text-sm font-semibold text-slate-800 group-hover:text-navy-800">{{ $c->title }}</span>
                            <span class="mt-0.5 block text-xs text-slate-500"><span
                                    class="font-mono font-semibold text-navy-700">{{ $c->ticket_number }}</span>
                                · {{ $c->reporter?->name }} · {{ $c->created_at->diffForHumans() }}</span>
                        </span>
                        <span class="flex shrink-0 items-center gap-2"><x-priority :priority="$c->priority" /><x-status
                                :status="$c->status" /></span>
                    </a>
                @empty
                    <x-empty-state title="Belum ada laporan" />
                @endforelse
            </div>
            <div class="mt-3 border-t border-slate-100 pt-3"><a href="{{ route('complaints.index') }}"
                    class="link-arrow text-sm font-semibold text-navy-700 hover:text-navy-900">Lihat semua laporan
                    <span class="arrow inline-block">→</span></a></div>
        </x-card>

        <div class="space-y-4">
            <x-card title="Per kategori">
                @forelse($byKategori as $k)
                    @php($max = max(1, $byKategori->max('complaints_count')))
                    <div class="py-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-600">{{ $k->name }}</span>
                            <span class="font-bold tabular-nums text-slate-900">{{ $k->complaints_count }}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                            @php($pct = round(($k->complaints_count / $max) * 100))
                            <div @class([
                                'h-full rounded-full bg-gradient-to-r from-navy-800 to-brass-500',
                                'w-0' => $pct <= 0,
                                'w-5' => $pct > 0 && $pct < 10,
                                'w-10' => $pct >= 10 && $pct < 20,
                                'w-1/5' => $pct >= 20 && $pct < 30,
                                'w-1/4' => $pct >= 30 && $pct < 40,
                                'w-1/3' => $pct >= 40 && $pct < 50,
                                'w-1/2' => $pct >= 50 && $pct < 60,
                                'w-3/5' => $pct >= 60 && $pct < 70,
                                'w-3/4' => $pct >= 70 && $pct < 80,
                                'w-4/5' => $pct >= 80 && $pct < 90,
                                'w-11/12' => $pct >= 90 && $pct < 100,
                                'w-full' => $pct >= 100,
                            ]) aria-hidden="true"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada data.</p>
                @endforelse
            </x-card>
            <x-card title="Tren 6 bulan">
                @forelse($bulanan as $b)
                    <div class="flex items-center justify-between border-b border-slate-50 py-2 text-sm last:border-0">
                        <span class="font-mono text-xs text-slate-500">{{ $b->bulan }}</span>
                        <span class="font-bold tabular-nums">{{ $b->total }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada data.</p>
                @endforelse
            </x-card>
        </div>
    </div>

    <div class="reveal mt-6 grid gap-4 delay-200 sm:grid-cols-3">
        @foreach ([['Monitoring penanganan', 'Laporan terbuka berdasar prioritas.', 'admin.monitoring'], ['Laporan administratif', 'Filter & ekspor CSV.', 'reports.index'], ['Kelola pengguna', 'Akun, role, aktivasi.', 'admin.users.index']] as $t)
            <a href="{{ route($t[2]) }}"
                class="lift lift-soft card-shadow group rounded-2xl border border-slate-200/80 bg-white/95 p-5">
                <p class="link-arrow flex items-center justify-between text-sm font-semibold text-slate-900">
                    {{ $t[0] }}
                    <span class="arrow text-navy-600">→</span>
                </p>
                <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $t[1] }}</p>
            </a>
        @endforeach
    </div>
@endsection
