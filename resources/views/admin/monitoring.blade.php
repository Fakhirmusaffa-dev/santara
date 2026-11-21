@extends('layouts.app')
@section('title', 'Monitoring Penanganan')
@section('page-title', 'Monitoring')
@section('page-subtitle', 'Laporan terbuka, diurutkan berdasar urgensi.')

@section('content')
    <x-card>
        <form method="GET" class="flex gap-2">
            <select name="status" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua terbuka</option>
                @foreach (['menunggu_verifikasi', 'terverifikasi', 'menunggu_penugasan', 'ditugaskan', 'sedang_ditangani', 'menunggu_pemeriksaan'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-navy-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
        </form>
    </x-card>
    <x-card class="mt-4">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead>
                    <tr class="border-b text-xs uppercase text-slate-500">
                        <th class="py-2 pr-4">Tiket</th>
                        <th class="py-2 pr-4">Judul</th>
                        <th class="py-2 pr-4">Prioritas</th>
                        <th class="py-2 pr-4">Petugas</th>
                        <th class="py-2 pr-4">Umur</th>
                        <th class="py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($complaints as $c)
                        <tr class="border-b border-slate-50 last:border-0">
                            <td class="py-3 pr-4 font-mono text-xs font-bold"><a class="text-navy-700"
                                    href="{{ route('complaints.show', $c) }}">{{ $c->ticket_number }}</a></td>
                            <td class="py-3 pr-4">{{ \Illuminate\Support\Str::limit($c->title, 45) }}</td>
                            <td class="py-3 pr-4"><x-priority :priority="$c->priority" /></td>
                            <td class="py-3 pr-4 text-xs">{{ $c->assignee?->name ?? '—' }}</td>
                            <td
                                class="py-3 pr-4 text-xs {{ $c->created_at->diffInDays() >= 7 ? 'font-bold text-red-600' : '' }}">
                                {{ $c->created_at->diffForHumans() }}</td>
                            <td class="py-3"><x-status :status="$c->status" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6"><x-empty-state title="Tidak ada laporan terbuka" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $complaints->links() }}</div>
    </x-card>
@endsection
