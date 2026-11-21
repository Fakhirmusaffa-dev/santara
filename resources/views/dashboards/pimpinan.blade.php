@extends('layouts.app')
@section('title', 'Dashboard Pimpinan')
@section('page-title', 'Pengawasan & Evaluasi')
@section('page-subtitle', 'Ringkasan, tren, dan persetujuan yang membutuhkan perhatian.')

@section('content')
    <div class="reveal grid grid-cols-2 gap-4 delay-40 lg:grid-cols-4">
        <x-stat title="Total Terpantau" :value="$total" accent="navy" icon="M3 3v18h18M7 14l4-4 3 3 5-6" />
        <x-stat title="Prioritas Tinggi" :value="$prioritasTinggi" hint="Belum selesai" accent="gold"
            icon="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        <x-stat title="Menunggu Persetujuan" :value="$menungguPersetujuan" accent="slate"
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat title="Tingkat Penyelesaian" :value="$penyelesaian . '%'" accent="navy"
            icon="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
    </div>

    <div class="reveal mt-6 grid gap-4 delay-120 lg:grid-cols-3">
        <x-card title="Butuh perhatian (prioritas tinggi)" class="lg:col-span-2">
            <div class="-mx-1">
                @forelse($butuhPerhatian as $c)
                    <a href="{{ route('complaints.show', $c) }}"
                        class="row-hover group flex items-center justify-between gap-3 rounded-xl px-3 py-3 hover:bg-slate-50">
                        <span class="min-w-0">
                            <span
                                class="block truncate text-sm font-semibold text-slate-800 group-hover:text-navy-800">{{ $c->isSensitive() ? '[Laporan terbatas]' : $c->title }}</span>
                            <span class="mt-0.5 block text-xs text-slate-500">{{ $c->ticket_number }} ·
                                {{ $c->category?->name }} · {{ $c->created_at->diffForHumans() }}</span>
                        </span>
                        <span class="flex shrink-0 items-center gap-2"><x-priority :priority="$c->priority" /><x-status
                                :status="$c->status" /></span>
                    </a>
                @empty
                    <x-empty-state title="Tidak ada laporan prioritas tinggi yang terbuka" />
                @endforelse
            </div>
            <div class="mt-3 border-t border-slate-100 pt-3"><a href="{{ route('approvals.index') }}"
                    class="link-arrow text-sm font-semibold text-navy-700 hover:text-navy-900">Ke halaman persetujuan
                    <span class="arrow inline-block">→</span></a></div>
        </x-card>

        <x-card title="Distribusi kategori">
            @forelse($distribusiKategori as $k)
                @php($pct = $total > 0 ? round(($k->complaints_count / $total) * 100) : 0)
                <div class="py-2.5">
                    <div class="flex items-baseline justify-between text-sm">
                        <span class="text-slate-600">{{ $k->name }}</span>
                        <span class="text-xs tabular-nums text-slate-400">{{ $pct }}% ·
                            <strong class="text-sm text-slate-900">{{ $k->complaints_count }}</strong></span>
                    </div>
                    <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
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
    </div>

    <div class="reveal mt-6 delay-200">
        <x-card title="Status keseluruhan">
            <div class="flex flex-wrap gap-2">
                @foreach ($byStatus as $status => $jumlah)
                    <span
                        class="lift-soft lift flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs ring-1 ring-slate-200/80">
                        <x-status :status="$status" /> <strong class="tabular-nums">{{ $jumlah }}</strong>
                    </span>
                @endforeach
                @if ($byStatus->isEmpty())
                    <p class="text-sm text-slate-500">Belum ada data.</p>
                @endif
            </div>
        </x-card>
    </div>
@endsection
