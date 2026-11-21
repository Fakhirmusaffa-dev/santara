@props(['label' => '', 'tone' => 'slate'])
@php
    $tones = [
        'slate' => 'bg-slate-100/90 text-slate-700 ring-slate-900/10',
        'navy' => 'bg-navy-900/[0.07] text-navy-800 ring-navy-900/15',
        'green' => 'bg-emerald-50 text-emerald-800 ring-emerald-900/15',
        'amber' => 'bg-amber-50 text-amber-800 ring-amber-900/15',
        'red' => 'bg-red-50 text-red-700 ring-red-900/15',
        'blue' => 'bg-sky-50 text-sky-800 ring-sky-900/15',
        'pink' => 'bg-pink-50 text-pink-800 ring-pink-900/15',
        'grey' => 'bg-slate-100/90 text-slate-700 ring-slate-900/10',
    ];
    $tone = $tones[$tone] ?? $tones['slate'];
@endphp
<span
    {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-semibold tracking-wide ring-1 ring-inset backdrop-blur $tone"]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>{{ $slot ?: $label }}
</span>
