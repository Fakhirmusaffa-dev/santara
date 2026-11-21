@extends('layouts.app')
@section('title', 'Audit Log')
@section('page-title', 'Riwayat Aktivitas')
@section('page-subtitle', 'Catatan append-only — tidak dapat diubah pengguna.')

@section('content')
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead>
                    <tr class="border-b text-xs uppercase text-slate-500">
                        <th class="py-2 pr-4">Waktu</th>
                        <th class="py-2 pr-4">Aktor</th>
                        <th class="py-2 pr-4">Aksi</th>
                        <th class="py-2">Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $l)
                        <tr class="border-b border-slate-50 last:border-0">
                            <td class="py-2.5 pr-4 text-xs text-slate-500">{{ $l->created_at->format('d M Y H:i:s') }}</td>
                            <td class="py-2.5 pr-4 text-xs">{{ $l->user?->email ?? 'sistem' }}</td>
                            <td class="py-2.5 pr-4"><x-badge tone="slate">{{ $l->action }}</x-badge></td>
                            <td class="py-2.5 text-xs">{{ $l->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6"><x-empty-state title="Belum ada aktivitas" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $logs->links() }}</div>
    </x-card>
@endsection
