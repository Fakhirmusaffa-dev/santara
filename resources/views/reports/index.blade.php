@extends('layouts.app')
@section('title', 'Laporan Administratif')
@section('page-title', 'Laporan')
@section('page-subtitle', 'Rekap filter, statistik, dan ekspor CSV.')

@section('content')
    <x-card>
        <form method="GET" class="grid gap-3 md:grid-cols-7">
            <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tiket / judul…"
                class="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2">
            <select name="status" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua status</option>
                @foreach (['menunggu_verifikasi', 'terverifikasi', 'menunggu_penugasan', 'ditugaskan', 'sedang_ditangani', 'menunggu_pemeriksaan', 'selesai', 'ditolak', 'ditutup'] as $s)
                    <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <select name="category_id" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Kategori</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected(($filters['category_id'] ?? '') == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <select name="location_id" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Lokasi</option>
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
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}"
                class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}"
                class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
            <div class="flex gap-2 md:col-span-7">
                <button class="rounded-xl bg-navy-900 px-4 py-2 text-sm font-semibold text-white">Terapkan</button>
                <a href="{{ route('reports.index') }}"
                    class="rounded-xl border border-slate-300 px-4 py-2 text-sm">Reset</a>
                <a href="{{ route('reports.export', request()->query()) }}"
                    class="ml-auto rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold hover:bg-slate-50">Ekspor
                    CSV</a>
            </div>
        </form>
    </x-card>

    <div class="mt-4 grid grid-cols-3 gap-4">
        <x-stat title="Hasil Filter" :value="$stats['total']" />
        <x-stat title="Selesai" :value="$stats['selesai']" />
        <x-stat title="Rata Feedback" :value="$stats['rata_feedback'] . '/5'" />
    </div>

    <x-card class="mt-4">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="py-2 pr-4">Tiket</th>
                        <th class="py-2 pr-4">Judul</th>
                        <th class="py-2 pr-4">K/L</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2">Dibuat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr class="border-b border-slate-50 last:border-0">
                            <td class="py-3 pr-4 font-mono text-xs font-bold"><a class="text-navy-700"
                                    href="{{ route('complaints.show', $r) }}">{{ $r->ticket_number }}</a></td>
                            <td class="py-3 pr-4">
                                {{ \Illuminate\Support\Str::limit($r->isSensitive() ? '[Terbatas]' : $r->title, 50) }}</td>
                            <td class="py-3 pr-4 text-xs">{{ $r->category?->name }} / {{ $r->location?->name }}</td>
                            <td class="py-3 pr-4"><x-status :status="$r->status" /></td>
                            <td class="py-3 text-xs">{{ $r->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6"><x-empty-state title="Tidak ada hasil" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $rows->links() }}</div>
    </x-card>

    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5">
        <button onclick="window.print()" class="rounded-xl bg-navy-900 px-4 py-2 text-sm font-semibold text-white">Cetak
            halaman ini</button>
        <p class="mt-2 text-xs text-slate-500">Gunakan dialog cetak browser untuk menyimpan sebagai PDF. Data sensitif
            otomatis disamarkan.</p>
    </div>
@endsection
