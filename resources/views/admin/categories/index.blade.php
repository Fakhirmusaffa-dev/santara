@extends('layouts.app')
@section('title', 'Kelola Kategori')
@section('page-title', 'Kategori Laporan')
@section('page-subtitle', 'Tambah, ubah, nonaktifkan, dan hapus kategori.')

@section('content')
    <div class="grid gap-4 lg:grid-cols-3">
        <x-card title="Tambah kategori" class="lg:col-span-1">
            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-3">
                @csrf
                <div><label class="text-sm font-medium">Nama *</label><input name="name" value="{{ old('name') }}"
                        required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"
                        placeholder="Contoh: Jaringan Internet"></div>
                <div><label class="text-sm font-medium">Deskripsi</label><input name="description"
                        value="{{ old('description') }}"
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
                <button class="w-full rounded-xl bg-navy-900 px-4 py-2 text-sm font-semibold text-white">Tambah</button>
            </form>
        </x-card>
        <x-card title="Daftar kategori" class="lg:col-span-2">
            <form method="GET" class="mb-3 flex gap-2">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama…"
                    class="flex-1 rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <button class="rounded-xl bg-navy-900 px-4 py-2 text-sm font-semibold text-white">Cari</button>
                @if (!empty($filters['search']))
                    <a href="{{ route('admin.categories.index') }}"
                        class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold">Reset</a>
                @endif
            </form>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                            <th class="py-2 pr-4">Kategori</th>
                            <th class="py-2 pr-4">Laporan</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $c)
                            <tr class="border-b border-slate-50 last:border-0">
                                <td class="py-3 pr-4">
                                    <a href="{{ route('admin.categories.show', $c) }}"
                                        class="font-semibold text-navy-700 hover:underline">{{ $c->name }}</a>
                                    <p class="text-xs text-slate-500">
                                        {{ \Illuminate\Support\Str::limit($c->description ?? '—', 50) }}</p>
                                </td>
                                <td class="py-3 pr-4 text-xs tabular-nums">{{ $c->complaints_count }}</td>
                                <td class="py-3 pr-4"><x-badge
                                        :tone="$c->is_active ? 'green' : 'red'">{{ $c->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                                <td class="py-3 text-right">
                                    <div class="flex justify-end gap-2 text-xs font-semibold">
                                        <a href="{{ route('admin.categories.show', $c) }}"
                                            class="text-navy-700 hover:underline">Detail</a>
                                        <a href="{{ route('admin.categories.edit', $c) }}"
                                            class="text-slate-700 hover:underline">Ubah</a>
                                        <form method="POST" action="{{ route('admin.categories.toggle', $c) }}"
                                            class="inline">@csrf
                                            <button
                                                class="{{ $c->is_active ? 'text-amber-700' : 'text-emerald-700' }} hover:underline">{{ $c->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $c) }}"
                                            class="inline">@csrf @method('DELETE')
                                            <button data-confirm="Hapus kategori {{ $c->name }}?"
                                                class="text-red-600 hover:underline">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6"><x-empty-state title="Belum ada kategori"
                                        hint="Tambahkan kategori lewat formulir di samping." /></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $categories->links() }}</div>
        </x-card>
    </div>
@endsection
