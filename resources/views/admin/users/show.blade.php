@extends('layouts.app')
@section('title', 'Detail Pengguna')
@section('page-title', $user->name)
@section('page-subtitle', $user->email)

@section('content')
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.users.index') }}"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">←
            Kembali</a>
        <a href="{{ route('admin.users.edit', $user) }}"
            class="rounded-xl bg-navy-900 px-4 py-2 text-sm font-semibold text-white hover:bg-navy-800">Ubah</a>
        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
            @csrf @method('DELETE')
            <button data-confirm="Hapus akun ini? Hanya akun yang tidak dipakai data lain yang bisa dihapus."
                class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">Hapus</button>
        </form>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <x-card title="Akun" class="lg:col-span-1">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">Role</dt>
                    <dd class="mt-1"><x-badge tone="navy">{{ $user->roleLabel() }}</x-badge></dd>
                </div>
                <div>
                    <dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">Status</dt>
                    <dd class="mt-1"><x-badge :tone="$user->is_active ? 'green' : 'red'">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">Jabatan</dt>
                    <dd class="mt-1 font-semibold text-slate-800">{{ $user->position ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">Telepon</dt>
                    <dd class="mt-1 text-slate-700">{{ $user->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">Login terakhir</dt>
                    <dd class="mt-1 text-slate-700">{{ $user->last_login_at?->format('d M Y H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">Dibuat</dt>
                    <dd class="mt-1 text-slate-700">{{ $user->created_at->format('d M Y H:i') }}</dd>
                </div>
            </dl>
            <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="mt-4">
                @csrf
                <button data-confirm="Ubah status akun ini?"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold {{ $user->is_active ? 'text-amber-700' : 'text-emerald-700' }}">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
            </form>
        </x-card>
        <x-card title="Aktivitas" class="lg:col-span-2">
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="rounded-xl bg-slate-50 px-3 py-4">
                    <p class="text-2xl font-bold tabular-nums text-slate-800">{{ $user->complaints_count }}</p>
                    <p class="mt-1 text-xs text-slate-500">Laporan dibuat</p>
                </div>
                <div class="rounded-xl bg-slate-50 px-3 py-4">
                    <p class="text-2xl font-bold tabular-nums text-slate-800">{{ $user->assigned_complaints_count }}</p>
                    <p class="mt-1 text-xs text-slate-500">Laporan ditangani</p>
                </div>
                <div class="rounded-xl bg-slate-50 px-3 py-4">
                    <p class="text-2xl font-bold tabular-nums text-slate-800">{{ $user->handling_logs_count }}</p>
                    <p class="mt-1 text-xs text-slate-500">Catatan progres</p>
                </div>
            </div>
            <p class="mt-4 text-xs leading-relaxed text-slate-500">Catatan: akun yang masih punya laporan, penugasan, atau
                catatan penanganan tidak dapat dihapus permanen — gunakan tombol Nonaktifkan agar riwayat tetap utuh.</p>
        </x-card>
    </div>
@endsection
