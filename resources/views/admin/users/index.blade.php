@extends('layouts.app')
@section('title', 'Kelola Pengguna')
@section('page-title', 'Pengguna')
@section('page-subtitle', 'Akun, role, dan status aktivasi.')

@section('content')
    <x-card>
        <div class="flex flex-col gap-3 sm:flex-row">
            <form method="GET" class="flex flex-1 gap-2">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama / email…"
                    class="flex-1 rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <select name="role" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Semua role</option>
                    @foreach (['pelapor', 'petugas', 'admin', 'pimpinan'] as $r)
                        <option value="{{ $r }}" @selected(($filters['role'] ?? '') === $r)>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
                <button class="rounded-xl bg-navy-900 px-4 py-2 text-sm font-semibold text-white">Cari</button>
            </form>
            <a href="{{ route('admin.users.create') }}"
                class="rounded-xl bg-emerald-700 px-4 py-2 text-center text-sm font-semibold text-white">+ Tambah
                pengguna</a>
        </div>
    </x-card>

    <x-card class="mt-4">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="py-2 pr-4">Nama</th>
                        <th class="py-2 pr-4">Role</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Login terakhir</th>
                        <th class="py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                        <tr class="border-b border-slate-50 last:border-0">
                            <td class="py-3 pr-4">
                                <p class="font-medium">{{ $u->name }}</p>
                                <p class="text-xs text-slate-500">{{ $u->email }}</p>
                            </td>
                            <td class="py-3 pr-4"><x-badge tone="navy">{{ $u->roleLabel() }}</x-badge></td>
                            <td class="py-3 pr-4"><x-badge
                                    :tone="$u->is_active ? 'green' : 'red'">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                            <td class="py-3 pr-4 text-xs">{{ $u->last_login_at?->diffForHumans() ?? '—' }}</td>
                            <td class="py-3">
                                <div class="flex gap-2 text-xs font-semibold">
                                    <a href="{{ route('admin.users.show', $u) }}" class="text-navy-700 hover:underline">Detail</a>
                                    <a href="{{ route('admin.users.edit', $u) }}" class="text-slate-700 hover:underline">Ubah</a>
                                    <form method="POST" action="{{ route('admin.users.toggle', $u) }}">@csrf
                                        <button data-confirm="Ubah status akun ini?"
                                            class="{{ $u->is_active ? 'text-amber-700' : 'text-emerald-700' }} hover:underline">{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.destroy', $u) }}">@csrf @method('DELETE')
                                        <button data-confirm="Hapus akun {{ $u->name }}?"
                                            class="text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $users->links() }}</div>
    </x-card>
@endsection
