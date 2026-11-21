@extends('layouts.app')
@section('title', 'Kelola Lokasi')
@section('page-title', 'Lokasi Kejadian')
@section('page-subtitle', 'Tambah, ubah, nonaktifkan, dan hapus lokasi.')

@section('content')
    <div class="grid gap-4 lg:grid-cols-3">
        <x-card title="Tambah lokasi" class="lg:col-span-1">
            <form method="POST" action="{{ route('admin.locations.store') }}" class="space-y-3">
                @csrf
                <div><label class="text-sm font-medium">Nama *</label><input name="name" value="{{ old('name') }}"
                        required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"
                        placeholder="Contoh: Aula Utama"></div>
                <div><label class="text-sm font-medium">Kode</label><input name="code" value="{{ old('code') }}"
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="AUL-01"></div>
                <div><label class="text-sm font-medium">Area *</label>
                    <select name="area" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        @foreach ($areas as $key => $label)
                            <option value="{{ $key }}" @selected(old('area', 'umum') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="text-sm font-medium">Deskripsi</label><input name="description"
                        value="{{ old('description') }}"
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
                <button class="w-full rounded-xl bg-navy-900 px-4 py-2 text-sm font-semibold text-white">Tambah</button>
            </form>
        </x-card>
        <x-card title="Daftar lokasi" class="lg:col-span-2">
            <form method="GET" class="mb-3 flex gap-2">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama / kode…"
                    class="flex-1 rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <select name="area" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Semua area</option>
                    @foreach ($areas as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['area'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded-xl bg-navy-900 px-4 py-2 text-sm font-semibold text-white">Cari</button>
                @if (!empty($filters['search']) || !empty($filters['area']))
                    <a href="{{ route('admin.locations.index') }}"
                        class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold">Reset</a>
                @endif
            </form>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                            <th class="py-2 pr-4">Lokasi</th>
                            <th class="py-2 pr-4">Area</th>
                            <th class="py-2 pr-4">Laporan</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($locations as $l)
                            <tr class="border-b border-slate-50 last:border-0">
                                <td class="py-3 pr-4">
                                    <a href="{{ route('admin.locations.show', $l) }}"
                                        class="font-semibold text-navy-700 hover:underline">{{ $l->name }}</a>
                                    @if ($l->code)
                                        <span class="font-mono text-xs text-slate-500">{{ $l->code }}</span>
                                    @endif
                                    @if ($l->description)
                                        <p class="text-xs text-slate-500">
                                            {{ \Illuminate\Support\Str::limit($l->description, 50) }}</p>
                                    @endif
                                </td>
                                <td class="py-3 pr-4"><x-badge
                                        :tone="$l->area === 'putra' ? 'blue' : ($l->area === 'putri' ? 'pink' : 'grey')">{{ $l->areaLabel() }}</x-badge></td>
                                <td class="py-3 pr-4 text-xs tabular-nums">{{ $l->complaints_count }}</td>
                                <td class="py-3 pr-4"><x-badge
                                        :tone="$l->is_active ? 'green' : 'red'">{{ $l->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                                <td class="py-3 text-right">
                                    <div class="flex justify-end gap-2 text-xs font-semibold">
                                        <a href="{{ route('admin.locations.show', $l) }}"
                                            class="text-navy-700 hover:underline">Detail</a>
                                        <a href="{{ route('admin.locations.edit', $l) }}"
                                            class="text-slate-700 hover:underline">Ubah</a>
                                        <form method="POST" action="{{ route('admin.locations.toggle', $l) }}"
                                            class="inline">@csrf
                                            <button
                                                class="{{ $l->is_active ? 'text-amber-700' : 'text-emerald-700' }} hover:underline">{{ $l->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.locations.destroy', $l) }}"
                                            class="inline">@csrf @method('DELETE')
                                            <button data-confirm="Hapus lokasi {{ $l->name }}?"
                                                class="text-red-600 hover:underline">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6"><x-empty-state title="Belum ada lokasi"
                                        hint="Tambahkan lokasi lewat formulir di samping." /></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $locations->links() }}</div>
        </x-card>
    </div>
@endsection
