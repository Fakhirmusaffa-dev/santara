@props(['title' => 'Belum ada data', 'hint' => ''])
<div
    {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300/80 bg-gradient-to-b from-slate-50 to-white px-6 py-12 text-center']) }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80">
        <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12h6m-6 4h6M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
        </svg>
    </span>
    <p class="mt-4 text-sm font-semibold text-slate-800">{{ $title }}</p>
    @if ($hint)
        <p class="mt-1 max-w-sm text-xs leading-relaxed text-slate-500">{{ $hint }}</p>
    @endif
    {{ $slot }}
</div>
