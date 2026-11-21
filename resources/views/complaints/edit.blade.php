@extends('layouts.app')
@section('title', 'Ubah Laporan ' . $complaint->ticket_number)
@section('page-title', 'Ubah Laporan')
@section('page-subtitle', $complaint->ticket_number)

@section('content')
    <x-card title="Formulir perubahan">
        <form method="POST" action="{{ route('complaints.update', $complaint) }}" enctype="multipart/form-data"
            class="grid gap-5 md:grid-cols-2">
            @csrf @method('PUT')
            <div>
                <label class="text-sm font-medium">Kategori *</label>
                <select name="category_id" required
                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id', $complaint->category_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Lokasi *</label>
                <select name="location_id" required
                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                    @foreach (['putra' => 'Area Putra', 'putri' => 'Area Putri', 'umum' => 'Umum / Bersama'] as $areaKey => $areaLabel)
                        @if ($locations->where('area', $areaKey)->isNotEmpty())
                            <optgroup label="{{ $areaLabel }}">
                                @foreach ($locations->where('area', $areaKey) as $l)
                                    <option value="{{ $l->id }}" @selected(old('location_id', $complaint->location_id) == $l->id)>{{ $l->name }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-medium">Judul *</label>
                <input name="title" value="{{ old('title', $complaint->title) }}" required
                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-medium">Deskripsi *</label>
                <textarea name="description" rows="5" required
                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">{{ old('description', $complaint->description) }}</textarea>
            </div>
            <div>
                <label class="text-sm font-medium">Prioritas *</label>
                <select name="priority" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                    @foreach (['rendah', 'sedang', 'tinggi', 'mendesak'] as $p)
                        <option value="{{ $p }}" @selected(old('priority', $complaint->priority) === $p)>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Tambah foto</label>
                <input type="file" name="photos[]" accept="image/*" multiple data-preview="photo-preview"
                    class="mt-1 w-full rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-2.5 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-navy-900 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white">
                <div id="photo-preview" class="mt-3 flex flex-wrap gap-2"></div>
            </div>
            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end md:col-span-2">
                <a href="{{ route('complaints.show', $complaint) }}"
                    class="rounded-xl border border-slate-300 px-5 py-2.5 text-center text-sm font-semibold hover:bg-slate-50">Batal</a>
                <button class="rounded-xl bg-navy-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-navy-800">Simpan
                    perubahan</button>
            </div>
        </form>
    </x-card>
@endsection
