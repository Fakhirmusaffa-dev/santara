@extends('layouts.guest')
@section('title', 'Buat Laporan')
@section('page-title', 'Buat Laporan')

@section('content')
    {{-- Pengantar singkat: satu kalimat saja, agar pelapor tidak betah membaca sebelum mengisi. --}}
    <div
        class="reveal mb-2.5 rounded-2xl border border-white/70 bg-white/55 px-3.5 py-2.5 shadow-[0_10px_30px_-22px_rgba(11,20,38,0.5)] backdrop-blur-md sm:mb-5 sm:px-4 sm:py-3">
        <p class="text-[13px] font-semibold text-navy-900 sm:text-sm">Laporkan kerusakan dalam 30 detik.</p>
        <p class="mt-0.5 text-[11px] leading-relaxed text-slate-700 sm:text-xs">
            Cukup isi apa yang rusak dan di mana. Nama boleh dikosongkan bila ingin anonim.</p>
    </div>

    <x-card :title="null" class="liquid-form-card lift mx-auto w-full">
        {{-- Hanya 3 kolom wajib; sisanya opsional dan dilipat agar form terasa pendek. --}}
        <form method="POST" action="{{ route('guest.complaints.store') }}" enctype="multipart/form-data"
            data-loading="Mengirim laporan Anda…" class="space-y-4 sm:space-y-5">
            @csrf

            {{-- 1. Kategori: chip, bukan dropdown. Lebih cepat dipindai & disentuh. --}}
            <fieldset>
                <legend class="text-[13px] font-semibold text-slate-700">Apa yang rusak? <span class="text-red-500">*</span>
                </legend>
                <div class="mt-1.5 flex flex-wrap gap-1.5">
                    {{-- Select asli tetap ada (sumber data + validasi required), chip hanya representasi visual. --}}
                    <select id="category_id" name="category_id" required aria-label="Kategori"
                        class="absolute h-0 w-0 appearance-none border-0 p-0 opacity-0">
                        <option value="">— Pilih —</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                    {{-- Chip dibangun dari <option> agar daftar kategori tetap satu sumber (DB). --}}
                    <div id="category-chips" class="flex flex-wrap gap-1.5"></div>
                </div>
                @error('category_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </fieldset>

            {{-- 2. Lokasi --}}
            <div>
                <label for="location_id" class="text-[13px] font-semibold text-slate-700">Di mana? <span
                        class="text-red-500">*</span></label>
                <select id="location_id" name="location_id" required
                    class="liquid-field mt-1.5 w-full rounded-xl px-3.5 py-2.5 text-sm">
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

            {{-- 3. Judul --}}
            <div>
                <label for="title" class="text-[13px] font-semibold text-slate-700">Singkatnya apa? <span
                        class="text-red-500">*</span></label>
                <input id="title" name="title" value="{{ old('title') }}" required minlength="8" maxlength="180"
                    placeholder="Keran wastafel asrama putra bocor"
                    class="liquid-field mt-1.5 w-full rounded-xl px-3.5 py-2.5 text-sm">
                @error('title')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- 4. Keterangan --}}
            <div>
                <label for="description" class="text-[13px] font-semibold text-slate-700">Ceritakan sedikit <span
                        class="text-red-500">*</span></label>
                <textarea id="description" name="description" rows="3" required minlength="20" maxlength="5000"
                    placeholder="Sejak kapan, dan apa dampaknya?" class="liquid-field mt-1.5 w-full rounded-xl px-3.5 py-2.5 text-sm">{{ old('description') }}</textarea>
                <p class="mt-1 text-[11px] text-slate-500">Minimal 20 karakter.</p>
                @error('description')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- 5. Klasifikasi TIDAK dipilih pelapor.
                 Sistem yang menentukan dari kategori + isi laporan (lihat
                 App\Services\ComplaintClassifier), lalu menurunkan prioritasnya.
                 Yang ditampilkan di sini hanya barang bukti aturannya, agar pelapor
                 tidak bingung kenapa laporannya cepat/enggak ditangani. --}}
            <div class="classifier-note rounded-xl px-3 py-2.5">
                <p class="flex items-start gap-2 text-[11.5px] leading-relaxed text-slate-700">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-navy-700" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3l7 3v6c0 4.2-2.9 7.7-7 9-4.1-1.3-7-4.8-7-9V6l7-3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4" />
                    </svg>
                    <span>Anda <strong class="font-semibold text-navy-900">tidak perlu</strong> memilih
                        prioritas. Sistem menilai sendiri dari kategori dan isi laporan — perkara
                        keamanan atau yang menyangkut keselamatan santri langsung ditangani khusus
                        dan didahulukan.</span>
                </p>
            </div>

            {{-- 6. Opsional: dilipat agar tidak jadi beban visual. --}}
            <details class="group rounded-xl border border-white/60 bg-white/35 px-3 py-2.5">
                <summary class="cursor-pointer list-none text-[12.5px] font-semibold text-slate-700 marker:hidden">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 transition-transform group-open:rotate-90" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" />
                        </svg>
                        Tambah foto, nama, atau kontak <span class="font-normal text-slate-500">(opsional)</span>
                    </span>
                </summary>

                <div class="mt-3 space-y-3">
                    <div>
                        <label for="photos" class="text-[12.5px] font-semibold text-slate-700">Foto <span
                                class="font-normal text-slate-400">(maks 4)</span></label>
                        <input id="photos" type="file" name="photos[]" accept="image/*" multiple
                            data-preview="photo-preview"
                            class="liquid-field mt-1.5 w-full cursor-pointer rounded-xl px-3.5 py-2 text-[13px] text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-navy-900 file:px-3.5 file:py-1.5 file:text-[12px] file:font-semibold file:text-white hover:file:bg-navy-800">
                        <div id="photo-preview" class="mt-2 flex flex-wrap gap-2"></div>
                        @error('photos.*')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="reporter_name" class="text-[12.5px] font-semibold text-slate-700">Nama <span
                                    class="font-normal text-slate-400">(opsional)</span></label>
                            <input id="reporter_name" name="reporter_name" value="{{ old('reporter_name') }}"
                                maxlength="120" placeholder="Kosongkan untuk anonim"
                                class="liquid-field mt-1.5 w-full rounded-xl px-3.5 py-2.5 text-sm">
                            @error('reporter_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="reporter_contact" class="text-[12.5px] font-semibold text-slate-700">Kontak
                                <span class="font-normal text-slate-400">(opsional)</span></label>
                            <input id="reporter_contact" name="reporter_contact" value="{{ old('reporter_contact') }}"
                                maxlength="60" placeholder="WA / HP"
                                class="liquid-field mt-1.5 w-full rounded-xl px-3.5 py-2.5 text-sm">
                            @error('reporter_contact')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </details>

            <div class="flex flex-col gap-2 border-t border-white/40 pt-4 sm:flex-row-reverse sm:justify-start">
                <button type="submit"
                    class="liquid-action-primary btn-press w-full rounded-xl px-6 py-3 text-sm font-semibold sm:w-auto sm:py-2.5">Kirim
                    laporan</button>
                <a href="{{ route('home') }}"
                    class="liquid-action-ghost btn-press w-full rounded-xl px-5 py-3 text-center text-sm font-semibold sm:w-auto sm:py-2.5">Batal</a>
            </div>

            <p class="text-center text-xs text-slate-600">Sudah pernah melapor?
                <a href="{{ route('guest.complaints.track') }}"
                    class="font-semibold text-navy-700 underline decoration-brass-500/60 underline-offset-4 hover:text-navy-900">Cek
                    status lewat nomor tiket</a>
            </p>
        </form>
    </x-card>
@endsection
