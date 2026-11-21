@props(['priority' => ''])
@php
    $map = ['rendah' => 'slate', 'sedang' => 'blue', 'tinggi' => 'amber', 'mendesak' => 'red'];
@endphp
<x-badge :tone="$map[$priority] ?? 'slate'">{{ ucfirst($priority) }}</x-badge>
