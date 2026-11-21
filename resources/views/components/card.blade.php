@props(['title' => '', 'action' => null])
{{-- Bila pemanggil memberi class sendiri (mis. liquid-form-card), class bawaan kartu tidak dipakai
     agar permukaan kaca tidak tertimpa latar putih pekat. --}}
@php
    $hasCustomClass = filled($attributes->get('class'));
@endphp
<section
    {{ $attributes->merge(
        [
            'class' => $hasCustomClass
                ? 'lift card-shadow rounded-2xl'
                : 'lift card-shadow rounded-2xl border border-slate-200/80 bg-white/95 backdrop-blur',
        ],
        false,
    ) }}>
    @if ($title)
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
            <h2 class="text-sm font-semibold tracking-tight text-slate-900">{{ $title }}</h2>
            @if ($action)
                <div class="shrink-0">{{ $action }}</div>
            @endif
        </div>
    @endif
    <div class="card-body p-4 sm:p-6">{{ $slot }}</div>
</section>
