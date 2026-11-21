@extends('layouts.app')
@section('title', 'Detail Lokasi')
@section('page-title', $location->name)
@section('page-subtitle', ($location->code ? $location->code . ' · ' : '') . ($location->is_active ? 'Aktif' : 'Nonaktif'))

@section('content')
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.locations.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">← Kembali</a>
        <a href="{{ route('admin.locations.edit', $location) }}" class="rounded-xl bg-navy-900 px-4 py-2 text-sm font-semibold text-white hover:bg-navy-800">Ubah</a>
        <form method="POST" action="{{ route('admin.locations.destroy', $location) }}" class="inline">
            @csrf @method('DELETE')
            <button data-confirm="Hapus lokasi ini?" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">Hapus</button>
        </form>
    </div>

    <x-card title="Informasi lokasi" class="mt-4">
        <dl class="grid gap-4 text-sm sm:grid-cols-2">
            <div><dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">Nama</dt><dd class="mt-1 font-semibold text-slate-800">{{ $location->name }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">Kode</dt><dd class="mt-1 font-mono text-slate-800">{{ $location->code ?? '—' }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">Area</dt><dd class="mt-1"><x-badge :tone="$location->area === 'putra' ? 'blue' : ($location->area === 'putri' ? 'pink' : 'grey')">{{ $location->areaLabel() }}</x-badge></dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">Status</dt><dd class="mt-1"><x-badge :tone="$location->is_active ? 'green' : 'red'">{{ $location->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">Total laporan</dt><dd class="mt-1 font-semibold text-slate-800">{{ $location->complaints_count }} laporan</dd></div>
            <div class="sm:col-span-2"><dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">Deskripsi</dt><dd class="mt-1 text-slate-700">{{ $location->description ?? '—' }}</dd></div>
        </dl>
    </x-card>

    <x-card title="Laporan di lokasi ini" class="mt-4">
        @forelse($complaints as $c)
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 py-3 last:border-0">
                <div>
                    <a href="{{ route('complaints.show', $c) }}" class="font-mono text-xs font-bold text-navy-700 hover:underline">{{ $c->ticket_number }}</a>
                    <p class="text-sm text-slate-700">{{ \Illuminate\Support\Str::limit($c->title, 60) }}</p>
                    <p class="text-xs text-slate-500">{{ $c->category?->name ?? '—' }} · {{ $c->reporter?->name ?? '—' }} · {{ $c->created_at->format('d M Y') }}</p>
                </div>
                <x-status :status="$c->status" />
            </div>
        @empty
            <x-empty-state title="Belum ada laporan" hint="Lokasi ini belum dipakai laporan mana pun." />
        @endforelse
        <div class="mt-4">{{ $complaints->links() }}</div>
    </x-card>
@endsection
