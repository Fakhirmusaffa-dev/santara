@extends('layouts.app')
@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')
@section('page-subtitle', 'Kabar terbaru atas laporan Anda.')

@section('content')
    <x-card>
        <div class="mb-4 flex items-center justify-between">
            <p class="text-sm text-slate-600">{{ auth()->user()->unreadNotifications()->count() }} belum dibaca</p>
            <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button
                    class="text-sm font-semibold text-navy-700 hover:underline">Tandai semua dibaca</button></form>
        </div>
        @forelse($notifications as $n)
            <a href="{{ route('notifications.read', $n->id) }}"
                class="flex gap-3 rounded-xl border px-4 py-3 text-sm transition hover:bg-slate-50 {{ is_null($n->read_at) ? 'border-navy-200 bg-navy-50/40' : 'border-slate-100' }}">
                <span
                    class="mt-1 h-2 w-2 shrink-0 rounded-full {{ is_null($n->read_at) ? 'bg-navy-700' : 'bg-slate-300' }}"></span>
                <span>
                    <span class="block font-semibold">{{ $n->data['title'] ?? 'Notifikasi' }}</span>
                    <span class="block text-slate-600">{{ $n->data['message'] ?? '' }}</span>
                    <span class="mt-1 block text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
                </span>
            </a>
            <div class="h-2"></div>
        @empty
            <x-empty-state title="Tidak ada notifikasi" />
        @endforelse
        <div class="mt-4">{{ $notifications->links() }}</div>
    </x-card>
@endsection
