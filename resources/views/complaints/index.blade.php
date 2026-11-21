@extends('layouts.app')
@section('title', 'Daftar Laporan')
@section('page-title', auth()->user()->isRole('pelapor') ? 'Laporan Saya' : 'Daftar Laporan')
@section('page-subtitle', 'Cari, saring, dan kelola laporan dengan tertib.')

@section('content')
    <x-card class="reveal delay-40">
        <form method="GET" class="grid gap-3 md:grid-cols-6">
            <div class="relative md:col-span-2">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                </svg>
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}"
                    placeholder="Cari tiket / judul…"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/60 py-2.5 pl-10 pr-3 text-sm placeholder:text-slate-400">
            </div>
            <select name="status"
                class="rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-700">
                <option value="">Semua status</option>
                @foreach (['menunggu_verifikasi' => 'Menunggu Verifikasi', 'terverifikasi' => 'Terverifikasi', 'menunggu_penugasan' => 'Menunggu Penugasan', 'ditugaskan' => 'Ditugaskan', 'sedang_ditangani' => 'Sedang Ditangani', 'menunggu_pemeriksaan' => 'Menunggu Pemeriksaan', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak', 'ditutup' => 'Ditutup'] as $v => $l)
                    <option value="{{ $v }}" @selected(($filters['status'] ?? '') === $v)>{{ $l }}</option>
                @endforeach
            </select>
            <select name="priority"
                class="rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-700">
                <option value="">Semua prioritas</option>
                @foreach (['rendah', 'sedang', 'tinggi', 'mendesak'] as $p)
                    <option value="{{ $p }}" @selected(($filters['priority'] ?? '') === $p)>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
            <select name="category_id"
                class="rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-700">
                <option value="">Semua kategori</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected(($filters['category_id'] ?? '') == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <select name="location_id"
                class="rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm text-slate-700">
                <option value="">Semua lokasi</option>
                @foreach (['putra' => 'Area Putra', 'putri' => 'Area Putri', 'umum' => 'Umum / Bersama'] as $areaKey => $areaLabel)
                    @if ($locations->where('area', $areaKey)->isNotEmpty())
                        <optgroup label="{{ $areaLabel }}">
                            @foreach ($locations->where('area', $areaKey) as $l)
                                <option value="{{ $l->id }}" @selected(($filters['location_id'] ?? '') == $l->id)>{{ $l->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                @endforeach
            </select>
            <div class="flex gap-2">
                <button
                    class="btn-press flex-1 rounded-xl bg-navy-900 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_12px_24px_-12px_rgba(20,32,56,0.7)] hover:bg-navy-800">Cari</button>
                <a href="{{ route('complaints.index') }}"
                    class="btn-press rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </x-card>

    <x-card class="reveal mt-4 delay-120">
        <div class="-mx-2 overflow-x-auto px-2">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead>
                    <tr class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">
                        <th class="py-2.5 pr-4">Tiket</th>
                        <th class="py-2.5 pr-4">Judul</th>
                        <th class="py-2.5 pr-4">Kategori</th>
                        <th class="py-2.5 pr-4">Prioritas</th>
                        <th class="py-2.5 pr-4">Status</th>
                        <th class="py-2.5 text-right">Diperbarui</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($complaints as $c)
                        <tr class="row-hover border-t border-slate-100 hover:bg-slate-50/80">
                            <td class="py-3.5 pr-4"><a href="{{ route('complaints.show', $c) }}"
                                    class="font-mono text-xs font-bold text-navy-700 hover:text-navy-900 hover:underline">{{ $c->ticket_number }}</a>
                                @if ($c->isSensitive())
                                    <span
                                        class="ml-1.5 rounded-md bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold tracking-wide text-amber-800 ring-1 ring-amber-900/10">TERBATAS</span>
                                @endif
                            </td>
                            <td class="max-w-64 py-3.5 pr-4">
                                <a href="{{ route('complaints.show', $c) }}"
                                    class="font-medium text-slate-800 hover:text-navy-800 hover:underline">{{ \Illuminate\Support\Str::limit($c->isSensitive() && auth()->user()->cannot('viewSensitive', $c) ? '[Judul dibatasi]' : $c->title, 60) }}</a>
                                <span
                                    class="mt-0.5 block text-xs text-slate-500">{{ ($c->reporter?->name ?? ($c->is_anonymous || ! $c->reporter_name ? 'Anonim' : $c->reporter_name)) . ' · ' . $c->created_at->format('d M Y') }}</span>
                            </td>
                            <td class="py-3.5 pr-4 text-xs text-slate-600">{{ $c->category?->name ?? '-' }}</td>
                            <td class="py-3.5 pr-4"><x-priority :priority="$c->priority" /></td>
                            <td class="py-3.5 pr-4"><x-status :status="$c->status" /></td>
                            <td class="py-3.5 text-right text-xs tabular-nums text-slate-500">
                                {{ $c->updated_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6"><x-empty-state title="Tidak ada laporan ditemukan"
                                    hint="Ubah kata kunci atau filter pencarian." /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 border-t border-slate-100 pt-4">{{ $complaints->links() }}</div>
    </x-card>
@endsection
