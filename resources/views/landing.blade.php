<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SANTARA — Sistem Aduan dan Pelaporan Terpadu</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-santara.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="ios-type relative min-h-screen bg-navy-950 text-slate-800 antialiased">
    <div class="relative isolate flex min-h-screen flex-col overflow-hidden">
        <div class="pointer-events-none fixed inset-0 -z-10 auth-backdrop bg-cover bg-center"></div>
        <div class="pointer-events-none fixed inset-0 -z-10 bg-navy-950/70"></div>
        <div
            class="pointer-events-none fixed inset-0 -z-10 [background-image:radial-gradient(1000px_480px_at_110%_-10%,rgba(185,151,91,0.16),transparent_60%),radial-gradient(900px_520px_at_-10%_110%,rgba(76,100,137,0.28),transparent_60%)]">
        </div>

        <header class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-5 py-5 sm:px-8 lg:px-12">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="SANTARA - Beranda">
                <img src="{{ asset('images/logo-santara.png') }}" alt="Logo SANTARA"
                    class="h-12 w-12 rounded-2xl bg-white object-contain p-1.5 shadow-sm ring-1 ring-slate-200">
                <span>
                    <span class="block text-sm font-extrabold tracking-[0.16em] text-white">SANTARA</span>
                    <span
                        class="mt-0.5 block text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-300">Ponpes
                        Al-Ittihad</span>
                </span>
            </a>
            <nav class="flex items-center gap-3 sm:gap-5" aria-label="Navigasi utama">
                <a href="#tentang"
                    class="hidden text-sm font-semibold text-white/85 transition hover:text-brass-300 sm:inline-flex">
                    Tentang layanan
                </a>
                <a href="{{ route('login') }}"
                    class="btn-press rounded-xl border-slate-200 bg-white/80 px-4 py-2.5 text-[13px] font-semibold text-slate-700 shadow-sm transition hover:border-navy-300 hover:text-navy-900">
                    Masuk staf
                </a>
            </nav>
        </header>

        <main
            class="mx-auto grid w-full max-w-7xl flex-1 items-center gap-12 px-5 pb-14 pt-8 sm:px-8 lg:grid-cols-[1.05fr_0.95fr] lg:px-12 lg:py-16">
            <section class="reveal max-w-2xl delay-60">
                <span
                    class="inline-flex items-center gap-2 rounded-full border-emerald-900/10 bg-white/80 px-3.5 py-1.5 text-xs font-semibold text-emerald-800 shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Layanan pelaporan Ponpes Al-Ittihad
                </span>
                <h1
                    class="mt-6 text-4xl font-extrabold leading-[1.12] tracking-tight text-white sm:text-5xl lg:text-[3.6rem]">
                    Ada yang perlu
                    <span
                        class="bg-gradient-to-r from-brass-700 to-brass-500 bg-clip-text text-transparent">disampaikan?</span>
                </h1>
                <p class="mt-5 max-w-xl text-base leading-7 text-slate-300 sm:text-lg">
                    Sampaikan laporan dengan mudah, aman, dan tanpa perlu membuat akun. Setiap laporan akan tercatat dan
                    ditindaklanjuti oleh pihak terkait.
                </p>

                <div class="mt-9 grid gap-4 sm:grid-cols-2">
                    <a href="{{ route('guest.complaints.create') }}"
                        class="group ios-glass-dark btn-press rounded-2xl p-5 text-white transition hover:-translate-y-0.5 hover:bg-navy-900/75">
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-brass-300 ring-1 ring-white/10">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5" />
                            </svg>
                        </span>
                        <span class="mt-5 block text-lg font-bold">Buat laporan</span>
                        <span class="mt-1.5 block text-sm leading-relaxed text-slate-300">Laporkan kejadian atau
                            kebutuhan tanpa login.</span>
                        <span class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-brass-300">
                            Mulai melapor
                            <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                            </svg>
                        </span>
                    </a>

                    <a href="{{ route('guest.complaints.track') }}"
                        class="group ios-glass-light btn-press rounded-2xl p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-brass-400 hover:shadow-md">
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-brass-500/10 text-brass-700 ring-1 ring-brass-700/10">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="11" cy="11" r="7" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20 20-4-4m-5-8v6m-3-3h6" />
                            </svg>
                        </span>
                        <span class="mt-5 block text-lg font-bold text-navy-950">Cek status laporan</span>
                        <span class="mt-1.5 block text-sm leading-relaxed text-slate-500">Pantau perkembangan
                            menggunakan nomor tiket.</span>
                        <span class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-navy-800">
                            Lacak laporan
                            <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                            </svg>
                        </span>
                    </a>
                </div>

                <div
                    class="ios-glass-light mt-7 flex items-start gap-3 rounded-2xl p-4 text-sm leading-relaxed text-slate-600 shadow-sm">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700" fill="none" stroke="currentColor"
                        stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3 5 6v5c0 4.6 3 8.2 7 10 4-1.8 7-5.4 7-10V6l-7-3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4" />
                    </svg>
                    <p><span class="font-semibold text-slate-800">Privasi Anda dihargai.</span> Nama bisa dikosongkan
                        jika ingin melapor secara anonim. Simpan nomor tiket untuk memantau laporan.</p>
                </div>
            </section>

            <aside class="reveal relative mx-auto w-full max-w-lg delay-160">
                <div
                    class="absolute -inset-5 -z-10 rounded-[2.5rem] bg-gradient-to-br from-brass-500/15 via-white/20 to-navy-800/10 blur-2xl">
                </div>
                <div class="ios-glass-light overflow-hidden rounded-[2rem] p-6 sm:p-8">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-brass-700">Mudah & transparan
                            </p>
                            <h2 class="mt-2 text-xl font-bold tracking-tight text-navy-950">Alur pelaporan</h2>
                        </div>
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-navy-950 text-brass-300">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                            </svg>
                        </span>
                    </div>

                    <ol class="mt-7 space-y-5">
                        @foreach ([['01', 'Kirim laporan', 'Ceritakan kejadian dan sertakan foto bila diperlukan.'], ['02', 'Simpan nomor tiket', 'Nomor tiket diberikan setelah laporan berhasil dikirim.'], ['03', 'Pantau tindak lanjut', 'Cek status laporan kapan saja tanpa harus login.']] as [$number, $title, $description])
                            <li class="flex gap-4">
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-navy-900/[0.06] text-xs font-extrabold tracking-wide text-navy-800 ring-1 ring-navy-900/10">{{ $number }}</span>
                                <div class="pt-0.5">
                                    <p class="text-sm font-bold text-slate-800">{{ $title }}</p>
                                    <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ $description }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    <div class="mt-7 border-t border-slate-100 pt-5 text-xs leading-relaxed text-slate-500">
                        Untuk pengelolaan dan tindak lanjut laporan, staf pondok dapat masuk melalui tombol <span
                            class="font-semibold text-slate-700">Masuk staf</span>.
                    </div>
                </div>
                <p class="mt-4 text-center text-[11px] font-medium tracking-wide text-slate-300">Sistem Aduan dan
                    Pelaporan Terpadu · PONPES AL-ITTIHAD</p>
            </aside>

            <section id="tentang" class="reveal space-y-8 delay-220 lg:col-span-2">
                <div class="mx-auto max-w-3xl text-center">
                    <span
                        class="inline-flex items-center rounded-full border-white/15 bg-white/10 px-3.5 py-1.5 text-xs font-semibold text-brass-300">
                        Kenali layanan SANTARA
                    </span>
                    <h2 class="mt-4 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Suara Anda membantu pondok menjadi lebih baik
                    </h2>
                    <p class="mt-4 text-sm leading-7 text-slate-300 sm:text-base">
                        SANTARA adalah Sistem Aduan dan Pelaporan Terpadu Ponpes Al-Ittihad. Melalui layanan ini,
                        pelapor dapat menyampaikan hal yang membutuhkan perhatian dengan alur yang jelas, lalu
                        memantau perkembangannya menggunakan nomor tiket.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <article class="ios-glass-light rounded-2xl p-5 sm:p-6">
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-brass-500/10 text-brass-700 ring-1 ring-brass-700/10">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 21h18M5 21V7l8-4v18m6 0V11l-6-4M9 9v.01M9 12v.01M9 15v.01M9 18v.01M15 14v.01M15 17v.01" />
                            </svg>
                        </span>
                        <h3 class="mt-4 text-base font-bold text-navy-950">Fasilitas pondok</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Sampaikan kerusakan atau kondisi fasilitas yang memerlukan perbaikan dan perhatian.
                        </p>
                    </article>
                    <article class="ios-glass-light rounded-2xl p-5 sm:p-6">
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-brass-500/10 text-brass-700 ring-1 ring-brass-700/10">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 3v3m0 12v3m9-9h-3M6 12H3m15.36-6.36-2.12 2.12M7.76 16.24l-2.12 2.12m12.72 0-2.12-2.12M7.76 7.76 5.64 5.64M8 12h8m-4-4v8" />
                            </svg>
                        </span>
                        <h3 class="mt-4 text-base font-bold text-navy-950">Kebutuhan sarana</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Beri tahu pihak terkait tentang kebutuhan sarana yang mendukung kegiatan di pondok.
                        </p>
                    </article>
                    <article class="ios-glass-light rounded-2xl p-5 sm:p-6">
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-brass-500/10 text-brass-700 ring-1 ring-brass-700/10">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3m0 4h.01M10.3 3.86 2.82 17a2 2 0 0 0 1.74 3h14.88a2 2 0 0 0 1.74-3L13.7 3.86a2 2 0 0 0-3.48 0Z" />
                            </svg>
                        </span>
                        <h3 class="mt-4 text-base font-bold text-navy-950">Kejadian yang perlu perhatian</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Laporkan kejadian yang membutuhkan tindak lanjut agar dapat ditangani oleh pihak terkait.
                        </p>
                    </article>
                </div>

                <div class="grid gap-4 lg:grid-cols-[0.8fr_1.2fr]">
                    <div class="ios-glass-dark rounded-2xl p-6 sm:p-7">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-brass-300">Sebelum mengirim</p>
                        <h3 class="mt-3 text-xl font-bold text-white">Agar laporan mudah ditindaklanjuti</h3>
                        <ul class="mt-5 space-y-3 text-sm leading-6 text-slate-200">
                            <li class="flex gap-3"><span class="text-brass-300">01</span><span>Jelaskan kejadian atau
                                    kebutuhan dengan ringkas dan jelas.</span></li>
                            <li class="flex gap-3"><span class="text-brass-300">02</span><span>Sertakan foto atau
                                    informasi pendukung bila diperlukan.</span></li>
                            <li class="flex gap-3"><span class="text-brass-300">03</span><span>Simpan nomor tiket yang
                                    diberikan setelah laporan terkirim.</span></li>
                        </ul>
                    </div>
                    <div class="ios-glass-light rounded-2xl p-6 sm:p-7">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-brass-700">Pertanyaan umum</p>
                        <div class="mt-3 divide-y divide-slate-900/10">
                            <details class="group py-4" open>
                                <summary
                                    class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-bold text-navy-950">
                                    Apakah saya perlu membuat akun?
                                    <span class="text-lg text-brass-700 transition group-open:rotate-45"
                                        aria-hidden="true">+</span>
                                </summary>
                                <p class="mt-2 pr-6 text-sm leading-6 text-slate-600">
                                    Tidak. Anda dapat mengirim laporan tanpa login. Login hanya digunakan oleh staf
                                    pondok.
                                </p>
                            </details>
                            <details class="group py-4">
                                <summary
                                    class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-bold text-navy-950">
                                    Bagaimana cara memantau laporan?
                                    <span class="text-lg text-brass-700 transition group-open:rotate-45"
                                        aria-hidden="true">+</span>
                                </summary>
                                <p class="mt-2 pr-6 text-sm leading-6 text-slate-600">
                                    Gunakan menu “Cek status laporan” dan masukkan nomor tiket yang Anda simpan setelah
                                    mengirim laporan.
                                </p>
                            </details>
                            <details class="group py-4">
                                <summary
                                    class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-bold text-navy-950">
                                    Apakah nama wajib diisi?
                                    <span class="text-lg text-brass-700 transition group-open:rotate-45"
                                        aria-hidden="true">+</span>
                                </summary>
                                <p class="mt-2 pr-6 text-sm leading-6 text-slate-600">
                                    Nama dapat dikosongkan jika Anda ingin menyampaikan laporan secara anonim.
                                </p>
                            </details>
                        </div>
                    </div>
                </div>

                <div
                    class="ios-glass-light flex-col items-start justify-between gap-4 rounded-2xl p-5 sm:flex-row sm:items-center sm:p-6">
                    <div>
                        <h3 class="text-lg font-bold text-navy-950">Ada hal yang ingin disampaikan?</h3>
                        <p class="mt-1 text-sm leading-relaxed text-slate-600">Mulai kirim laporan, atau cek kembali
                            perkembangan laporan Anda.</p>
                    </div>
                    <div class="flex shrink-0 flex-wrap gap-3">
                        <a href="{{ route('guest.complaints.create') }}"
                            class="btn-press rounded-xl bg-navy-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-navy-800">
                            Buat laporan
                        </a>
                        <a href="{{ route('guest.complaints.track') }}"
                            class="btn-press rounded-xl border-navy-900/15 bg-white/60 px-4 py-2.5 text-sm font-semibold text-navy-900 transition hover:bg-white">
                            Cek status
                        </a>
                    </div>
                </div>
            </section>
        </main>
        <footer class="mx-auto w-full max-w-7xl px-5 pb-6 text-center text-xs text-slate-300 sm:px-8 lg:px-12">
            © {{ date('Y') }} SANTARA · Ponpes Al-Ittihad
        </footer>
    </div>

    <x-loader />
</body>

</html>
