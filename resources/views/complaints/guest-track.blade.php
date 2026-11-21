@extends('layouts.guest')
@section('title', 'Cek Status Laporan')

@section('content')
    <div
        class="reveal mb-2.5 rounded-2xl border border-white/70 bg-white/55 px-3.5 py-2.5 shadow-[0_10px_30px_-22px_rgba(11,20,38,0.5)] backdrop-blur-md sm:mb-5 sm:px-4 sm:py-3.5">
        <p class="text-[13px] font-semibold text-navy-900 sm:text-sm">Cek status laporan Anda.</p>
        <p class="mt-0.5 text-[11px] leading-relaxed text-slate-700 sm:text-xs">Masukkan nomor tiket (contoh:
            STR-20261117-ABC123) yang Anda dapat saat mengirim laporan.</p>
    </div>

    <x-card :title="null" class="liquid-form-card lift mx-auto w-full">
        <div class="mb-3 border-b border-white/35 pb-2.5 sm:mb-4 sm:pb-3">
            <h2 class="text-[15px] font-semibold tracking-tight text-slate-900 sm:text-sm">Pelacakan tiket</h2>
            <p class="mt-0.5 text-xs text-slate-600">Nomor tiket tidak peka huruf besar/kecil.</p>
        </div>

        <form method="GET" action="{{ route('guest.complaints.track') }}" class="flex flex-col gap-2.5 sm:flex-row">
            <input type="text" name="ticket" value="{{ old('ticket', $ticket) }}" required
                placeholder="Nomor tiket laporan" autocomplete="off"
                class="liquid-field w-full rounded-xl px-3.5 py-2.5 text-sm uppercase tracking-wide shadow-sm placeholder:text-slate-400">
            <button type="submit"
                class="liquid-action-primary btn-press shrink-0 rounded-xl bg-navy-900 px-6 py-2.5 text-sm font-semibold text-white">Cari</button>
        </form>

        <div class="mt-4 sm:mt-6">
            @if ($ticket !== '' && !$complaint)
                <div class="rounded-2xl border border-amber-900/10 bg-amber-50/60 px-4 py-3.5 text-sm text-amber-900">
                    <p class="font-semibold">Tiket tidak ditemukan.</p>
                    <p class="mt-1 text-xs leading-relaxed">Periksa kembali nomor tiket Anda. Pastikan formatnya benar,
                        misalnya <span class="font-semibold">STR-20261117-ABC123</span>.</p>
                </div>
            @elseif ($complaint)
                <div class="rounded-2xl border border-navy-900/10 bg-white/70 p-4 shadow-sm sm:p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Nomor tiket</p>
                            <p class="text-base font-bold tracking-wide text-navy-900">{{ $complaint->ticket_number }}</p>
                        </div>
                        <span
                            class="rounded-full bg-navy-900/[0.06] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.08em] text-navy-700 ring-1 ring-navy-900/10">{{ $complaint->statusLabel() }}</span>
                    </div>

                    <h2 class="mt-3 text-sm font-bold text-slate-900">{{ $complaint->title }}</h2>
                    <p class="mt-1 whitespace-pre-line text-[13px] leading-relaxed text-slate-600">
                        {{ $complaint->description }}</p>

                    <dl class="mt-4 grid grid-cols-2 gap-3 text-[13px] sm:grid-cols-3">
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Kategori</dt>
                            <dd class="mt-0.5 font-medium text-slate-700">{{ $complaint->category?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Lokasi</dt>
                            <dd class="mt-0.5 font-medium text-slate-700">{{ $complaint->location?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Prioritas</dt>
                            <dd class="mt-0.5 font-medium capitalize text-slate-700">{{ $complaint->priority }}</dd>
                        </div>
                    </dl>

                    <div class="mt-5 border-t border-slate-100 pt-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Riwayat status</p>
                        <ol class="mt-2.5 space-y-3">
                            @forelse ($complaint->statusHistories as $h)
                                <li class="flex gap-3">
                                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-navy-700"></span>
                                    <div class="min-w-0">
                                        <p class="text-[13px] font-semibold text-slate-800">{{ $complaint->statusLabel() }}
                                        </p>
                                        <p class="mt-0.5 text-[11px] text-slate-500">
                                            {{ $h->created_at->translatedFormat('d M Y, H:i') }} WIB
                                            @if ($h->note)
                                                · {{ $h->note }}
                                            @endif
                                        </p>
                                    </div>
                                </li>
                            @empty
                                <li class="text-[13px] text-slate-500">Belum ada riwayat status.</li>
                            @endforelse
                        </ol>
                    </div>
                </div>
            @endif
        </div>

        <p class="mt-5 text-center text-xs text-slate-500">Belum pernah melapor?
            <a href="{{ route('guest.complaints.create') }}"
                class="font-semibold text-navy-700 underline decoration-brass-500/60 underline-offset-4 hover:text-navy-900">Buat
                laporan baru</a>
        </p>
    </x-card>
@endsection
