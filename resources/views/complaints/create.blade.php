@extends('layouts.app')
@section('title', 'Buat Laporan Baru')
@section('page-title', 'Buat Laporan')
@section('page-subtitle', 'Sampaikan dengan jelas agar mudah ditindaklanjuti.')

@section('content')
    <x-card title="Formulir laporan" class="reveal mx-auto max-w-3xl delay-60">
        <form method="POST" action="{{ route('complaints.store') }}" enctype="multipart/form-data"
            data-loading="Mengirim laporan…" class="grid gap-5 md:grid-cols-2">
            @csrf
            <div>
                <label for="category_id" class="text-[13px] font-semibold text-slate-700">Kategori <span
                        class="text-red-500">*</span></label>
                <select id="category_id" name="category_id" required
                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm">
                    <option value="">— Pilih kategori —</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="location_id" class="text-[13px] font-semibold text-slate-700">Lokasi kejadian <span
                        class="text-red-500">*</span></label>
                <select id="location_id" name="location_id" required
                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm">
                    <option value="">— Pilih lokasi —</option>
                    @foreach (['putra' => 'Area Putra', 'putri' => 'Area Putri', 'umum' => 'Umum / Bersama'] as $areaKey => $areaLabel)
                        @if ($locations->where('area', $areaKey)->isNotEmpty())
                            <optgroup label="{{ $areaLabel }}">
                                @foreach ($locations->where('area', $areaKey) as $l)
                                    <option value="{{ $l->id }}" @selected(old('location_id') == $l->id)>{{ $l->name }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
                @error('location_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="md:col-span-2">
                <label for="title" class="text-[13px] font-semibold text-slate-700">Judul laporan <span
                        class="text-red-500">*</span></label>
                <input id="title" name="title" value="{{ old('title') }}" required minlength="8" maxlength="180"
                    placeholder="Contoh: Keran wastafel asrama putra bocor"
                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm placeholder:text-slate-400">
                @error('title')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="md:col-span-2">
                <label for="description" class="text-[13px] font-semibold text-slate-700">Deskripsi <span
                        class="text-red-500">*</span></label>
                <p class="mt-0.5 text-xs text-slate-500">Jelaskan apa yang terjadi, sejak kapan, dan dampaknya. Minimal
                    20 karakter.</p>
                <textarea id="description" name="description" rows="5" required minlength="20" maxlength="5000"
                    placeholder="Ceritakan kronologi dan kondisi saat ini…"
                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm placeholder:text-slate-400">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="priority" class="text-[13px] font-semibold text-slate-700">Prioritas <span
                        class="text-red-500">*</span></label>
                <select id="priority" name="priority" required
                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm">
                    @foreach (['rendah' => 'Rendah — bisa dijadwalkan', 'sedang' => 'Sedang — perlu penanganan', 'tinggi' => 'Tinggi — mengganggu aktivitas', 'mendesak' => 'Mendesak — bahaya / lumpuh'] as $v => $l)
                        <option value="{{ $v }}" @selected(old('priority', 'sedang') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="sensitivity" class="text-[13px] font-semibold text-slate-700">Klasifikasi <span
                        class="text-red-500">*</span></label>
                <select id="sensitivity" name="sensitivity" required
                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm">
                    <option value="biasa" @selected(old('sensitivity', 'biasa') === 'biasa')>Biasa — terlihat pengelola</option>
                    <option value="sensitif" @selected(old('sensitivity') === 'sensitif')>Sensitif — akses dibatasi (need-to-know)
                    </option>
                </select>
                <p class="mt-1 text-xs text-slate-500">Laporan sensitif hanya terlihat pihak yang ditunjuk.</p>
            </div>
            <div class="md:col-span-2">
                <label for="photos" class="text-[13px] font-semibold text-slate-700">Foto pendukung <span
                        class="font-normal text-slate-400">(maks 4, JPG/PNG/WebP ≤ 4MB)</span></label>
                <input id="photos" type="file" name="photos[]" accept="image/*" multiple data-preview="photo-preview"
                    class="mt-1.5 w-full cursor-pointer rounded-xl border border-dashed border-slate-300 bg-slate-50/80 px-3.5 py-3 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-navy-900 file:px-4 file:py-2 file:text-[13px] file:font-semibold file:text-white hover:file:bg-navy-800">
                <div id="photo-preview" class="mt-3 flex flex-wrap gap-2"></div>
                @error('photos.*')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div
                class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end md:col-span-2">
                <a href="{{ route('complaints.index') }}"
                    class="btn-press rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
                <button type="submit"
                    class="btn-press rounded-xl bg-navy-900 px-6 py-2.5 text-sm font-semibold text-white shadow-[0_12px_24px_-12px_rgba(20,32,56,0.7)] hover:bg-navy-800">Kirim
                    laporan</button>
            </div>
        </form>
    </x-card>
@endsection
