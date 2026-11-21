@extends('layouts.app')
@section('title', 'Profil Saya')
@section('page-title', 'Profil')
@section('page-subtitle', 'Kelola identitas dan keamanan akun.')

@section('content')
    <div class="grid gap-4 lg:grid-cols-2">
        <x-card title="Identitas">
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-3">
                @csrf @method('PUT')
                <div><label class="text-sm font-medium">Nama *</label><input name="name"
                        value="{{ old('name', $user->name) }}" required
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label class="text-sm font-medium">Email</label><input value="{{ $user->email }}" disabled
                        class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500">
                </div>
                <div><label class="text-sm font-medium">Role</label><input value="{{ $user->roleLabel() }}" disabled
                        class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500">
                </div>
                <div><label class="text-sm font-medium">Jabatan</label><input name="position"
                        value="{{ old('position', $user->position) }}"
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
                <div><label class="text-sm font-medium">Telepon</label><input name="phone"
                        value="{{ old('phone', $user->phone) }}"
                        class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
                <button class="rounded-xl bg-navy-900 px-4 py-2 text-sm font-semibold text-white">Simpan profil</button>
            </form>
        </x-card>
        <x-card title="Ubah kata sandi">
            <form method="POST" action="{{ route('profile.password') }}" class="space-y-3">
                @csrf @method('PUT')
                <div><label class="text-sm font-medium">Kata sandi saat ini *</label><input name="current_password"
                        type="password" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div><label class="text-sm font-medium">Kata sandi baru * (min 8)</label><input name="password"
                        type="password" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div><label class="text-sm font-medium">Konfirmasi baru *</label><input name="password_confirmation"
                        type="password" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button class="rounded-xl bg-navy-900 px-4 py-2 text-sm font-semibold text-white">Ubah kata sandi</button>
            </form>
        </x-card>
    </div>
@endsection
