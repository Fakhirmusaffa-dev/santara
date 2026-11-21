@extends('layouts.app')
@section('title', 'Feedback Pelapor')
@section('page-title', 'Feedback')
@section('page-subtitle', 'Evaluasi kepuasan atas laporan selesai.')

@section('content')
    <div class="grid grid-cols-2 gap-4">
        <x-stat title="Rata-rata Rating" :value="$rata . '/5'" />
        <x-stat title="Total Feedback" :value="$feedbacks->total()" />
    </div>
    <x-card title="Distribusi rating" class="mt-4">
        @for ($i = 5; $i >= 1; $i--)
            @php($count = $distribusi[$i] ?? 0)
            @php($pct = $feedbacks->total() ? round(($count / max($feedbacks->total(), 1)) * 100) : 0)
            <div class="flex items-center gap-3 py-1 text-sm">
                <span class="w-8 font-semibold">{{ $i }}★</span>
                <div class="h-2 flex-1 rounded-full bg-slate-100">
                    <div @class([
                        'h-2 rounded-full bg-amber-500',
                        'w-0' => $pct <= 0,
                        'w-5' => $pct > 0 && $pct < 10,
                        'w-10' => $pct >= 10 && $pct < 20,
                        'w-1/5' => $pct >= 20 && $pct < 30,
                        'w-1/4' => $pct >= 30 && $pct < 40,
                        'w-1/3' => $pct >= 40 && $pct < 50,
                        'w-1/2' => $pct >= 50 && $pct < 60,
                        'w-3/5' => $pct >= 60 && $pct < 70,
                        'w-3/4' => $pct >= 70 && $pct < 80,
                        'w-4/5' => $pct >= 80 && $pct < 90,
                        'w-11/12' => $pct >= 90 && $pct < 100,
                        'w-full' => $pct >= 100,
                    ]) aria-hidden="true"></div>
                </div>
                <span class="w-10 text-right text-xs text-slate-500">{{ $count }}</span>
            </div>
        @endfor
    </x-card>
    <x-card class="mt-4">
        @forelse($feedbacks as $f)
            <div class="border-b border-slate-100 py-3 text-sm last:border-0">
                <p class="font-semibold">{{ $f->complaint?->ticket_number }} — {{ $f->rating }}/5 <span
                        class="font-normal text-slate-500">oleh {{ $f->user?->name }}</span></p>
                @if ($f->comment)
                    <p class="text-slate-600">"{{ $f->comment }}"</p>
                @endif
            </div>
        @empty
            <x-empty-state title="Belum ada feedback" />
        @endforelse
        <div class="mt-4">{{ $feedbacks->links() }}</div>
    </x-card>
@endsection
