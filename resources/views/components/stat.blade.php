@props(['title' => '', 'value' => '-', 'hint' => '', 'icon' => null, 'accent' => 'navy'])
@php
    $accents = [
        'navy' => 'from-navy-900 to-navy-700 text-white',
        'gold' => 'from-brass-500 to-brass-700 text-white',
        'slate' => 'from-slate-700 to-slate-500 text-white',
    ];
    $tone = $accents[$accent] ?? $accents['navy'];
@endphp
<div
    {{ $attributes->merge(['class' => 'lift card-shadow group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/95 p-5']) }}>
    <div
        class="pointer-events-none absolute -right-10 -top-10 h-28 w-28 rounded-full bg-gradient-to-br {{ $tone }} opacity-[0.08] blur-2xl transition-opacity duration-500 group-hover:opacity-[0.16]">
    </div>
    <div class="relative flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">{{ $title }}</p>
            <p class="mt-1.5 truncate text-[26px] font-bold leading-none tracking-tight text-slate-900">
                {{ $value }}</p>
            @if ($hint)
                <p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
            @endif
        </div>
        @if ($icon)
            <span
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $tone }} shadow-lg shadow-navy-900/10 transition-transform duration-500 group-hover:-translate-y-0.5 group-hover:scale-[1.03]">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                </svg>
            </span>
        @endif
    </div>
</div>
