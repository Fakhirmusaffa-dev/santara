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
                <div>
                    <label class="text-sm font-medium" for="koordinator_role">Diteruskan otomatis ke</label>
                    <select id="koordinator_role" name="koordinator_role"
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        @foreach (\App\Services\ComplaintRouter::options() as $value => $label)
                            <option value="{{ $value }}" @selected(old('koordinator_role') === $value)>{{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Laporan kategori ini langsung masuk ke koordinator tersebut.</p>
                    <div data-koordinator-list
                        class="mt-2 space-y-1.5 rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2">
                        <p class="text-xs font-semibold text-slate-600">Koordinator per divisi</p>
                        @foreach (\App\Models\User::KOORDINATOR_ROLES as $role)
                            @php($divisi = $koordinators[$role] ?? collect())
                            <div class="flex flex-wrap items-center gap-1 text-xs">
                                <span class="font-medium text-slate-700">{{ \App\Models\User::ROLE_LABELS[$role] }}:</span>
                                @forelse ($divisi as $k)
                                    <span data-koordinator-role="{{ $role }}"
                                        class="rounded-full bg-white px-2 py-0.5 text-slate-600 ring-1 ring-slate-200">{{ $k->name }}</span>
                                @empty
                                    <span data-koordinator-role="{{ $role }}" class="text-slate-400 italic">belum ada
                                        koordinator aktif</span>
                                @endforelse
                            </div>
                        @endforeach
                    </div>
                </div>
                <label class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50/60 px-3 py-2.5 text-sm font-medium">
                    <input type="checkbox" name="is_sensitive" value="1" @checked(old('is_sensitive'))
                        class="mt-0.5 h-4 w-4 rounded">
                    <span>Wajib ditangani khusus (sensitif)</span>
                </label>
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
                            <th class="py-2 pr-4">Diteruskan ke</th>
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
                                <td class="py-3 pr-4 text-xs">
                                    @if ($c->koordinator_role)
                                        <x-badge tone="blue">{{ \App\Models\User::ROLE_LABELS[$c->koordinator_role] ?? $c->koordinator_role }}</x-badge>
                                        @php($divisi = $koordinators[$c->koordinator_role] ?? collect())
                                        <p class="mt-1 text-slate-500">
                                            {{ $divisi->isNotEmpty() ? $divisi->pluck('name')->join(', ') : 'belum ada koordinator aktif' }}</p>
                                    @else
                                        <span class="text-slate-500">Admin</span>
                                    @endif
                                    @if ($c->is_sensitive)
                                        <span class="ml-1 text-amber-700" title="Wajib ditangani khusus">●</span>
                                    @endif
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
                                <td colspan="5" class="py-6"><x-empty-state title="Belum ada kategori"
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
