@extends('layouts.app')
@section('title', 'Ubah Pengguna')
@section('page-title', 'Ubah Pengguna')
@section('page-subtitle', $user->email)

@section('content')
    <x-card title="Formulir perubahan">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid gap-4 md:grid-cols-2">
            @csrf @method('PUT')
            <div><label class="text-sm font-medium">Nama *</label><input name="name" value="{{ old('name', $user->name) }}"
                    required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-sm font-medium">Email *</label><input name="email" type="email"
                    value="{{ old('email', $user->email) }}" required
                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-sm font-medium">Kata sandi baru (kosongkan bila tidak diubah)</label><input
                    name="password" type="password"
                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-sm font-medium">Konfirmasi sandi baru</label><input name="password_confirmation"
                    type="password" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-sm font-medium">Role *</label>
                <select name="role" required @disabled((int) $user->id === (int) auth()->id())
                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    @foreach (\App\Models\User::STAFF_ROLES as $v)
                        @php($l = \App\Models\User::ROLE_LABELS[$v])
                        <option value="{{ $v }}" @selected(old('role', $user->role) === $v)>{{ $l }}</option>
                    @endforeach
                </select>
                @if ((int) $user->id === (int) auth()->id())
                    <p class="mt-1 text-xs text-slate-500">Role akun sendiri tidak dapat diubah.</p>
                @endif
            </div>
            <div><label class="text-sm font-medium">Jabatan</label><input name="position"
                    value="{{ old('position', $user->position) }}"
                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
            <div class="md:col-span-2"><label class="text-sm font-medium">Telepon</label><input name="phone"
                    value="{{ old('phone', $user->phone) }}"
                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
            @if ((int) $user->id !== (int) auth()->id())
                <label class="flex items-center gap-2 text-sm font-medium md:col-span-2"><input type="checkbox"
                        name="is_active" value="1" @checked(old('is_active', $user->is_active)) class="h-4 w-4 rounded"> Akun
                    aktif</label>
            @endif
            <div class="flex justify-end gap-2 md:col-span-2">
                <a href="{{ route('admin.users.show', $user) }}"
                    class="rounded-xl border border-slate-300 px-5 py-2 text-sm font-semibold">Batal</a>
                <button class="rounded-xl bg-navy-900 px-5 py-2 text-sm font-semibold text-white">Simpan perubahan</button>
            </div>
        </form>
    </x-card>
@endsection
