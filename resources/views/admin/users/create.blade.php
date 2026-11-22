@extends('layouts.app')
@section('title', 'Tambah Pengguna')
@section('page-title', 'Tambah Pengguna')

@section('content')
    <x-card title="Akun baru">
        <form method="POST" action="{{ route('admin.users.store') }}" class="grid gap-4 md:grid-cols-2">
            @csrf
            <div><label class="text-sm font-medium">Nama *</label><input name="name" value="{{ old('name') }}" required
                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-sm font-medium">Email *</label><input name="email" type="email"
                    value="{{ old('email') }}" required
                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-sm font-medium">Kata sandi * (min 8)</label><input name="password" type="password"
                    required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-sm font-medium">Konfirmasi sandi *</label><input name="password_confirmation"
                    type="password" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-sm font-medium">Role *</label>
                <select name="role" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    @foreach (\App\Models\User::STAFF_ROLES as $v)
                        @php($l = \App\Models\User::ROLE_LABELS[$v])
                        <option value="{{ $v }}" @selected(old('role') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div><label class="text-sm font-medium">Jabatan</label><input name="position" value="{{ old('position') }}"
                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
            <div class="md:col-span-2"><label class="text-sm font-medium">Telepon</label><input name="phone"
                    value="{{ old('phone') }}" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="flex justify-end gap-2 md:col-span-2">
                <a href="{{ route('admin.users.index') }}"
                    class="rounded-xl border border-slate-300 px-5 py-2 text-sm font-semibold">Batal</a>
                <button class="rounded-xl bg-navy-900 px-5 py-2 text-sm font-semibold text-white">Simpan akun</button>
            </div>
        </form>
    </x-card>
@endsection
