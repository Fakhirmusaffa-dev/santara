@props(['status' => ''])
@php
    $map = [
        'draft' => 'slate',
        'menunggu_verifikasi' => 'amber',
        'terverifikasi' => 'blue',
        'menunggu_penugasan' => 'amber',
        'ditugaskan' => 'navy',
        'sedang_ditangani' => 'blue',
        'menunggu_pemeriksaan' => 'amber',
        'selesai' => 'green',
        'ditolak' => 'red',
        'ditutup' => 'slate',
    ];
    $labels = [
        'draft' => 'Draft',
        'menunggu_verifikasi' => 'Menunggu Verifikasi',
        'terverifikasi' => 'Terverifikasi',
        'menunggu_penugasan' => 'Menunggu Penugasan',
        'ditugaskan' => 'Ditugaskan',
        'sedang_ditangani' => 'Sedang Ditangani',
        'menunggu_pemeriksaan' => 'Menunggu Pemeriksaan',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
        'ditutup' => 'Ditutup',
    ];
@endphp
<x-badge :tone="$map[$status] ?? 'slate'">{{ $labels[$status] ?? $status }}</x-badge>
