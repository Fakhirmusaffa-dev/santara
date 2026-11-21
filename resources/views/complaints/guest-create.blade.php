@extends('layouts.guest')
@section('title', 'Buat Laporan')
@section('page-title', 'Buat Laporan')

@section('content')
    {{-- Pengantar di atas latar ilustrasi terang (bukan foto gelap), jadi teksnya tinta navy pekat. --}}
    <div
        class="reveal mb-2.5 rounded-2xl border border-white/70 bg-white/55 px-3.5 py-2.5 shadow-[0_10px_30px_-22px_rgba(11,20,38,0.5)] backdrop-blur-md sm:mb-5 sm:px-4 sm:py-3.5">
        <p class="text-[13px] font-semibold text-navy-900 sm:text-sm">
            Sampaikan aduan Anda tanpa perlu akun.</p>
        <p class="mt-0.5 text-[11px] leading-relaxed text-slate-700 sm:text-xs">
            Isi formulir di bawah — laporan langsung masuk ke admin dan pimpinan. Nama boleh dikosongkan bila ingin
            anonim.</p>
    </div>

    {{-- Kartu form dengan permukaan liquid glass transparan: foto latar tetap terlihat di baliknya. --}}
    <x-card :title="null" class="liquid-form-card lift mx-auto w-full">
        <div class="mb-3 border-b border-white/35 pb-2.5 sm:mb-4 sm:pb-3">
            <h2 class="text-[15px] font-semibold tracking-tight text-slate-900 sm:text-sm">Formulir laporan</h2>
            <p class="mt-0.5 text-xs text-slate-600">Tanda <span class="text-red-500">*</span> wajib diisi.</p>
        </div>

        <form method="POST" action="{{ route('guest.complaints.store') }}" enctype="multipart/form-data"
            data-loading="Mengirim laporan Anda…"
            class="grid grid-cols-1 gap-3 sm:gap-5 md:grid-cols-2">
            @csrf
            <div>
                <label for="category_id" class="text-[13px] font-semibold text-slate-700">Kategori <span
                        class="text-red-500">*</span></label>
                <select id="category_id" name="category_id" required
                    class="liquid-field mt-1.5 w-full rounded-xl px-3.5 py-2.5 text-sm shadow-sm">
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
                    class="liquid-field mt-1.5 w-full rounded-xl px-3.5 py-2.5 text-sm shadow-sm">
                    <option value="">— Pilih lokasi —</option>
                    @foreach (['putra' => 'Area Putra', 'putri' => 'Area Putri', 'umum' => 'Umum / Bersama'] as $areaKey => $areaLabel)
                        @if ($locations->where('area', $areaKey)->isNotEmpty())
                            <optgroup label="{{ $areaLabel }}">
                                @foreach ($locations->where('area', $areaKey) as $l)
                                    <option value="{{ $l->id }}" @selected(old('location_id') == $l->id)>{{ $l->name }}
                                    </option>
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
                    class="liquid-field mt-1.5 w-full rounded-xl px-3.5 py-2.5 text-sm shadow-sm placeholder:text-slate-400">
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
                    class="liquid-field mt-1.5 w-full rounded-xl px-3.5 py-2.5 text-sm shadow-sm placeholder:text-slate-400">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="reporter_name" class="text-[13px] font-semibold text-slate-700">Nama <span
                        class="font-normal text-slate-400">(opsional)</span></label>
                <input id="reporter_name" name="reporter_name" value="{{ old('reporter_name') }}" maxlength="120"
                    placeholder="Boleh dikosongkan untuk anonim"
                    class="liquid-field mt-1.5 w-full rounded-xl px-3.5 py-2.5 text-sm shadow-sm placeholder:text-slate-400">
                @error('reporter_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="reporter_contact" class="text-[13px] font-semibold text-slate-700">Kontak (WA/HP) <span
                        class="font-normal text-slate-400">(opsional)</span></label>
                <input id="reporter_contact" name="reporter_contact" value="{{ old('reporter_contact') }}" maxlength="60"
                    placeholder="Agar mudah dihubungi bila perlu"
                    class="liquid-field mt-1.5 w-full rounded-xl px-3.5 py-2.5 text-sm shadow-sm placeholder:text-slate-400">
                @error('reporter_contact')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="priority" class="text-[13px] font-semibold text-slate-700">Prioritas <span
                        class="text-red-500">*</span></label>
                <select id="priority" name="priority" required
                    class="liquid-field mt-1.5 w-full rounded-xl px-3.5 py-2.5 text-sm shadow-sm">
                    @foreach (['rendah' => 'Rendah — bisa dijadwalkan', 'sedang' => 'Sedang — perlu penanganan', 'tinggi' => 'Tinggi — mengganggu aktivitas', 'mendesak' => 'Mendesak — bahaya / lumpuh'] as $v => $l)
                        <option value="{{ $v }}" @selected(old('priority', 'sedang') === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="sensitivity" class="text-[13px] font-semibold text-slate-700">Klasifikasi <span
                        class="text-red-500">*</span></label>
                <select id="sensitivity" name="sensitivity" required
                    class="liquid-field mt-1.5 w-full rounded-xl px-3.5 py-2.5 text-sm shadow-sm">
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
                    class="liquid-field mt-1.5 w-full cursor-pointer rounded-xl px-3.5 py-3 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-navy-900 file:px-4 file:py-2 file:text-[13px] file:font-semibold file:text-white hover:file:bg-navy-800">
                <div id="photo-preview" class="mt-3 flex flex-wrap gap-2"></div>
                @error('photos.*')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="md:col-span-2 border-t border-white/40 pt-4 sm:pt-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <button type="submit"
                        class="liquid-action-primary btn-press order-1 w-full rounded-xl px-6 py-3 text-sm font-semibold text-white sm:order-2 sm:w-auto sm:py-2.5">Kirim
                        laporan</button>
                    <a href="{{ route('home') }}"
                        class="liquid-action-ghost btn-press order-2 w-full rounded-xl px-6 py-3 text-center text-sm font-semibold sm:order-1 sm:w-auto sm:py-2.5">Kembali</a>
                </div>
                <p class="mt-3 text-center text-xs text-slate-600">Sudah pernah melapor?
                    <a href="{{ route('guest.complaints.track') }}"
                        class="font-semibold text-navy-700 underline decoration-brass-500/60 underline-offset-4 hover:text-navy-900">Cek
                        status lewat nomor tiket</a>
                </p>
            </div>
        </form>
    </x-card>
@endsection
