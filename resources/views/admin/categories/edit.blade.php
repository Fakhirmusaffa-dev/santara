@extends('layouts.app')
@section('title', 'Ubah Kategori')
@section('page-title', 'Ubah Kategori')
@section('page-subtitle', $category->name)

@section('content')
    <x-card title="Formulir perubahan">
        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="grid gap-4 md:grid-cols-2">
            @csrf @method('PUT')
            <div><label class="text-sm font-medium">Nama *</label><input name="name"
                    value="{{ old('name', $category->name) }}" required
                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-sm font-medium">Deskripsi</label><input name="description"
                    value="{{ old('description', $category->description) }}"
                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"></div>
            <label class="flex items-center gap-2 text-sm font-medium md:col-span-2"><input type="checkbox" name="is_active"
                    value="1" @checked(old('is_active', $category->is_active)) class="h-4 w-4 rounded"> Aktif (tampil di form
                pengaduan)</label>
            <div class="md:col-span-2">
                <label class="text-sm font-medium" for="koordinator_role">Diteruskan otomatis ke</label>
                <select id="koordinator_role" name="koordinator_role"
                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    @foreach (\App\Services\ComplaintRouter::options() as $value => $label)
                        <option value="{{ $value }}" @selected(old('koordinator_role', $category->koordinator_role ?? '') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Laporan pada kategori ini langsung masuk ke koordinator
                    tersebut tanpa menunggu validasi admin. Kosongkan bila masih perlu divalidasi admin.</p>
            </div>

            <label class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50/60 px-3 py-2.5 text-sm font-medium md:col-span-2">
                <input type="checkbox" name="is_sensitive" value="1"
                    @checked(old('is_sensitive', $category->is_sensitive)) class="mt-0.5 h-4 w-4 rounded">
                <span>Wajib ditangani khusus
                    <span class="mt-0.5 block text-xs font-normal text-slate-600">Laporan pada kategori ini otomatis
                        diklasifikasikan sensitif dan berprioritas mendesak. Pelapor tidak dapat mengubahnya.</span>
                </span>
            </label>
            <div class="flex justify-end gap-2 md:col-span-2">
                <a href="{{ route('admin.categories.show', $category) }}"
                    class="rounded-xl border border-slate-300 px-5 py-2 text-sm font-semibold">Batal</a>
                <button class="rounded-xl bg-navy-900 px-5 py-2 text-sm font-semibold text-white">Simpan perubahan</button>
            </div>
        </form>
    </x-card>
@endsection
