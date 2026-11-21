@extends('layouts.app')
@section('title', 'Persetujuan')
@section('page-title', 'Persetujuan')
@section('page-subtitle', 'Pengajuan penyelesaian & keputusan pimpinan.')

@section('content')
    <x-card>
        <form method="GET" class="flex gap-2">
            <select name="status" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua</option>
                @foreach (['menunggu', 'disetujui', 'ditolak'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-navy-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
        </form>
    </x-card>

    <x-card title="Daftar pengajuan" class="mt-4">
        @forelse($approvals as $a)
            <div class="border-b border-slate-100 py-4 last:border-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono text-xs font-bold">{{ $a->complaint?->ticket_number }}</span>
                    <x-badge :tone="$a->status === 'menunggu' ? 'amber' : ($a->status === 'disetujui' ? 'green' : 'red')">{{ ucfirst($a->status) }}</x-badge>
                    <x-badge tone="slate">{{ ucfirst($a->type) }}</x-badge>
                    <span class="ml-auto text-xs text-slate-500">{{ $a->created_at->diffForHumans() }}</span>
                </div>
                <p class="mt-2 text-sm font-medium">{{ $a->complaint?->title }}</p>
                <p class="text-xs text-slate-500">Diajukan {{ $a->requester?->name }}: {{ $a->request_note }}</p>
                @if ($a->decision_note)
                    <p class="mt-1 text-xs text-slate-600">Keputusan: {{ $a->decision_note }}</p>
                @endif
                @if ($a->status === 'menunggu')
                    <form method="POST" action="{{ route('approvals.decide', $a) }}"
                        class="mt-3 grid gap-2 sm:grid-cols-3">
                        @csrf
                        <select name="decision" required class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="disetujui">Disetujui (selesai)</option>
                            <option value="ditolak">Ditolak (kembalikan)</option>
                        </select>
                        <input name="decision_note" required minlength="5" placeholder="Catatan keputusan…"
                            class="rounded-xl border border-slate-300 px-3 py-2 text-sm sm:col-span-2">
                        <button
                            class="rounded-xl bg-navy-900 px-4 py-2 text-sm font-semibold text-white sm:col-span-3">Simpan
                            keputusan</button>
                    </form>
                @endif
                <a href="{{ route('complaints.show', $a->complaint) }}"
                    class="mt-2 inline-block text-xs font-semibold text-navy-700 hover:underline">Buka detail laporan →</a>
            </div>
        @empty
            <x-empty-state title="Tidak ada pengajuan" />
        @endforelse
        <div class="mt-4">{{ $approvals->links() }}</div>
    </x-card>
@endsection
