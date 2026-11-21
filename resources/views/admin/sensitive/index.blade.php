@extends('layouts.app')
@section('title', 'Sensitif Masuk')
@section('page-title', 'Sensitif Masuk')
@section('page-subtitle', 'Triase buta: hanya metadata. Isi laporan tetap terkunci sampai Anda diberi akses tercatat.')

@section('content')
    <div class="grid grid-cols-3 gap-4">
        <x-stat title="Total Sensitif" :value="$total" />
        <x-stat title="Tanpa Penangan" :value="$tanpaPenangan" />
        <x-stat title="Akses Saya" :value="$milikSaya" />
    </div>

    <x-card title="Antrean triase buta" class="mt-4">
        <p class="mb-4 rounded-xl bg-amber-50 px-4 py-3 text-xs leading-relaxed text-amber-800 ring-1 ring-amber-900/10">
            Yang tampil di sini hanya <strong>nomor tiket, prioritas, status, dan waktu masuk</strong>.
            Judul, isi, nama pelapor, kategori, lokasi, dan foto <strong>tidak ditampilkan</strong> sebelum Anda
            diberi akses tercatat — itu prinsip need-to-know.
        </p>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="py-2 pr-4">Tiket</th>
                        <th class="py-2 pr-4">Prioritas</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Masuk</th>
                        <th class="py-2 pr-4">Penanganan</th>
                        <th class="py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($antrean as $c)
                        <tr class="border-b border-slate-50 last:border-0">
                            <td class="py-3 pr-4 font-mono text-xs font-bold text-navy-700">{{ $c->ticket_number }}</td>
                            <td class="py-3 pr-4"><x-priority :priority="$c->priority" /></td>
                            <td class="py-3 pr-4"><x-status :status="$c->status" /></td>
                            <td class="py-3 pr-4 text-xs text-slate-500">{{ $c->created_at->format('d M Y H:i') }}</td>
                            <td class="py-3 pr-4 text-xs text-slate-600">
                                @if ($c->assigned_to)
                                    <span class="font-semibold text-slate-700">Sudah ditugaskan</span>
                                @else
                                    <span class="text-amber-700">Belum ditugaskan</span>
                                @endif
                                · {{ $c->accesses_count }} akses
                                @if ($c->milik_saya > 0)
                                    · <span class="font-semibold text-emerald-700">Saya punya akses</span>
                                @endif
                            </td>
                            <td class="py-3 text-right">
                                <div class="flex flex-wrap justify-end gap-2 text-xs font-semibold">
                                    @if ($c->milik_saya > 0)
                                        <a href="{{ route('complaints.show', $c) }}"
                                            class="rounded-xl bg-navy-900 px-3 py-1.5 text-white hover:bg-navy-800">Buka
                                            isi</a>
                                    @else
                                        <form method="POST" action="{{ route('complaints.grant-access', $c) }}"
                                            class="inline-flex items-center gap-1.5">@csrf
                                            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                                            <input type="hidden" name="reason"
                                                value="Triase buta: mengambil alih penanganan awal.">
                                            <button
                                                data-confirm="Ambil alih laporan {{ $c->ticket_number }}? Aksi ini tercatat di audit log."
                                                class="rounded-xl bg-emerald-700 px-3 py-1.5 text-white hover:bg-emerald-600">Ambil
                                                alih</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('complaints.grant-access', $c) }}"
                                        class="inline-flex items-center gap-1.5">@csrf
                                        <select name="user_id" required
                                            class="max-w-36 rounded-xl border border-slate-200 px-2 py-1.5 text-xs">
                                            <option value="">— Tunjuk —</option>
                                            @foreach ($handlers->where('id', '!=', auth()->id()) as $h)
                                                <option value="{{ $h->id }}">{{ $h->name }}
                                                    ({{ ucfirst($h->role) }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="reason" required minlength="5" maxlength="255"
                                            placeholder="Alasan (min 5)"
                                            class="w-36 rounded-xl border border-slate-200 px-2 py-1.5 text-xs">
                                        <button
                                            data-confirm="Beri akses laporan {{ $c->ticket_number }}? Tercatat di audit log."
                                            class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-slate-700 hover:bg-slate-50">Beri
                                            akses</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6"><x-empty-state title="Tidak ada laporan sensitif"
                                    hint="Antrean kosong — belum ada laporan sensitif masuk." /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $antrean->links() }}</div>
    </x-card>
@endsection
