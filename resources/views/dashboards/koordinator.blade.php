@extends('layouts.app')
@section('title', 'Dashboard ' . auth()->user()->roleLabel())
@section('page-title', 'Tugas Saya')
@section('page-subtitle', 'Laporan yang otomatis diteruskan ke divisi {{ auth()->user()->roleLabel() }}.')
@section('content')
    <div class="reveal grid grid-cols-2 gap-4 delay-40 lg:grid-cols-4">
        <x-stat title="Total Laporan" :value="$total" accent="navy"
            icon="M9 12h6m-6 4h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
        <x-stat title="Belum Ditangani" :value="$baru" hint="Baru masuk" accent="gold"
            icon="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
        <x-stat title="Sedang Ditangani" :value="$ditangani" accent="slate" icon="M13 10V3L4 14h7v7l9-11h-7z" />
        <x-stat title="Mendesak" :value="$mendesak" hint="Perlu didahulukan" accent="navy"
            icon="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
    </div>

    <x-card title="Laporan terbaru untuk divisi Anda" class="reveal mt-6 delay-120">
        <div class="-mx-2 overflow-x-auto px-2">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead>
                    <tr class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">
                        <th class="py-2.5 pr-4">Tiket</th>
                        <th class="py-2.5 pr-4">Judul</th>
                        <th class="py-2.5 pr-4">Prioritas</th>
                        <th class="py-2.5 pr-4">Status</th>
                        <th class="py-2.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tugas as $c)
                        <tr class="row-hover border-t border-slate-100 hover:bg-slate-50/80">
                            <td class="py-3.5 pr-4 font-mono text-xs font-semibold text-navy-700">{{ $c->ticket_number }}
                            </td>
                            <td class="max-w-56 py-3.5 pr-4 font-medium text-slate-800">
                                {{ \Illuminate\Support\Str::limit($c->title, 50) }}</td>
                            <td class="py-3.5 pr-4"><x-priority :priority="$c->priority" /></td>
                            <td class="py-3.5 pr-4"><x-status :status="$c->status" /></td>
                            <td class="py-3.5 text-right"><a href="{{ route('complaints.show', $c) }}"
                                    class="link-arrow font-semibold text-navy-700 hover:text-navy-900">Tangani <span
                                        class="arrow inline-block">→</span></a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6"><x-empty-state title="Belum ada laporan"
                                    hint="Laporan yang masuk ke divisi Anda akan otomatis tampil di sini." /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection
