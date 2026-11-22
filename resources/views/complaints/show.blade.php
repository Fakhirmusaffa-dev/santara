@extends('layouts.app')
@section('title', 'Tiket ' . $complaint->ticket_number)
@section('page-title', $complaint->ticket_number)
@section('page-subtitle', $complaint->category?->name . ' · ' . $complaint->location?->name)

@section('content')
    @if (session('complaint_preview'))
        @php($preview = session('complaint_preview'))
        <div class="reveal mb-4 rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 text-sm delay-20">
            <p class="inline-flex items-center gap-2 font-semibold text-emerald-900">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-[11px] text-white">✓</span>
                Laporan Anda tersimpan
            </p>
            <p class="mt-1 text-emerald-900/90">
                <strong>{{ $preview['title'] }}</strong><br>
                <span class="text-emerald-800/80">{{ $preview['category'] ?? '—' }} · {{ $preview['location'] ?? '—' }}</span><br>
                Status: <strong>{{ $preview['status'] }}</strong>
                @if ($preview['routed_to'])
                    · diteruskan ke <strong>{{ $preview['routed_to'] }}</strong>
                @endif
            </p>
        </div>
    @endif

    <div class="reveal flex flex-wrap items-center gap-2 delay-20">
        <x-status :status="$complaint->status" />
        <x-priority :priority="$complaint->priority" />
        @if ($complaint->isSensitive())
            <x-badge tone="amber">Laporan terbatas</x-badge>
        @endif
        <span class="ml-auto text-xs tabular-nums text-slate-500">Dibuat {{ $complaint->created_at->format('d M Y H:i') }}
            · diperbarui {{ $complaint->updated_at->diffForHumans() }}</span>
    </div>

    <div class="reveal mt-4 grid gap-4 delay-100 lg:grid-cols-3">
        <div class="min-w-0 space-y-4 lg:col-span-2">
            <x-card title="{{ $canViewSensitive ? $complaint->title : '[Judul dibatasi — akses terbatas]' }}">
                @if ($canViewSensitive)
                    <p class="whitespace-pre-line text-[15px] leading-relaxed text-slate-700">{{ $complaint->description }}
                    </p>
                @else
                    <p class="text-sm text-slate-500">Isi laporan disembunyikan karena klasifikasi sensitif. Anda hanya
                        melihat ringkasan status.</p>
                @endif

                <dl class="mt-5 grid grid-cols-2 gap-4 border-t border-slate-100 pt-5 text-sm sm:grid-cols-4">
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">Pelapor</dt>
                        <dd class="mt-1 font-semibold text-slate-800">
                            @if (! $canViewSensitive)
                                [Terbatas]
                            @elseif ($complaint->reporter)
                                {{ $complaint->reporter->name }}
                            @elseif ($complaint->is_anonymous || ! $complaint->reporter_name)
                                Anonim
                            @else
                                {{ $complaint->reporter_name }}
                                @if ($complaint->reporter_contact)
                                    <span class="block text-[11px] font-normal text-slate-500">{{ $complaint->reporter_contact }}</span>
                                @endif
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">Kategori</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ $complaint->category?->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">Lokasi</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ $complaint->location?->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-400">Petugas</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ $complaint->assignee?->name ?? '—' }}</dd>
                    </div>
                </dl>
            </x-card>

            @if ($canViewSensitive && $complaint->photos->isNotEmpty())
                <x-card title="Lampiran ({{ $complaint->photos->count() }})">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ($complaint->photos as $p)
                            <a href="{{ route('attachments.show', $p) }}" target="_blank"
                                class="lift-soft lift group overflow-hidden rounded-2xl border border-slate-200/80 bg-slate-50">
                                <div class="overflow-hidden">
                                    <img src="{{ route('attachments.show', $p) }}" alt="Lampiran"
                                        class="h-32 w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]"
                                        loading="lazy">
                                </div>
                                <p class="truncate px-2.5 py-2 text-[11px] text-slate-500">{{ $p->kind }} ·
                                    {{ number_format($p->size / 1024) }} KB</p>
                            </a>
                        @endforeach
                    </div>
                </x-card>
            @endif

            <x-card title="Progres penanganan ({{ $complaint->handlingLogs->count() }})">
                @forelse($complaint->handlingLogs as $h)
                    <div class="relative border-l-2 border-navy-200/70 pb-5 pl-4 last:pb-0">
                        <span
                            class="absolute -left-[5px] top-1.5 h-2 w-2 rounded-full bg-navy-700 ring-4 ring-navy-900/[0.06]"></span>
                        <p class="text-sm font-semibold text-slate-800">{{ $h->title ?? 'Catatan progres' }}
                            @if (!is_null($h->progress))
                                <span
                                    class="ml-2 rounded-full bg-navy-900/[0.06] px-2 py-0.5 text-xs font-semibold tabular-nums text-navy-800">{{ $h->progress }}%</span>
                            @endif
                        </p>
                        <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-slate-600">{{ $h->note }}</p>
                        <p class="mt-1.5 text-xs text-slate-400">{{ $h->user?->name }} ·
                            {{ $h->created_at->format('d M Y H:i') }}</p>
                        @if ($h->photo_path)
                            <a href="{{ route('attachments.handling', $h->photo_path) }}" target="_blank"
                                class="link-arrow mt-1.5 inline-block text-xs font-semibold text-navy-700 hover:text-navy-900">Lihat
                                dokumentasi <span class="arrow inline-block">→</span></a>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada catatan penanganan.</p>
                @endforelse

                @can('handle', $complaint)
                    <form method="POST" action="{{ route('complaints.handling', $complaint) }}" enctype="multipart/form-data"
                        class="mt-5 space-y-3 border-t border-slate-100 pt-5">
                        @csrf
                        <div class="grid gap-3 sm:grid-cols-2">
                            <input name="title" placeholder="Judul catatan (opsional)"
                                class="rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm placeholder:text-slate-400">
                            <input name="progress" type="number" min="0" max="100"
                                placeholder="Progres % (opsional)"
                                class="rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm placeholder:text-slate-400">
                        </div>
                        <textarea name="note" rows="3" required placeholder="Tulis progres / kendala…"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm placeholder:text-slate-400"></textarea>
                        <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
                            <input type="file" name="photo" accept="image/*"
                                class="w-full cursor-pointer text-sm text-slate-500 file:mr-3 file:rounded-xl file:border-0 file:bg-white file:px-3.5 file:py-2 file:text-[13px] file:font-semibold file:text-navy-800 file:ring-1 file:ring-slate-200 hover:file:bg-slate-50">
                            <button
                                class="btn-press shrink-0 rounded-xl bg-navy-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-navy-800">Simpan
                                progres</button>
                        </div>
                    </form>
                @endcan
            </x-card>

            @if ($complaint->feedback)
                <x-card title="Penilaian pelapor">
                    <div class="flex items-center gap-1.5" aria-label="Rating {{ $complaint->feedback->rating }} dari 5">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="h-5 w-5 {{ $i <= $complaint->feedback->rating ? 'text-brass-500' : 'text-slate-200' }}"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.9l-5.2 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z" />
                            </svg>
                        @endfor
                        <span
                            class="ml-1.5 text-sm font-bold tabular-nums text-slate-800">{{ $complaint->feedback->rating }}/5</span>
                    </div>
                    @if ($complaint->feedback->comment)
                        <p class="mt-2.5 border-l-2 border-brass-300 pl-3 text-sm italic leading-relaxed text-slate-600">
                            "{{ $complaint->feedback->comment }}"</p>
                    @endif
                </x-card>
            @elseif($complaint->status === 'selesai' && (int) $complaint->user_id === (int) auth()->id())
                <x-card title="Beri penilaian">
                    <form method="POST" action="{{ route('feedbacks.store', $complaint) }}" class="space-y-3.5">
                        @csrf
                        <div class="flex flex-wrap gap-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <label
                                    class="btn-press flex cursor-pointer items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-600 has-checked:border-navy-700 has-checked:bg-navy-900 has-checked:text-white">
                                    <input type="radio" name="rating" value="{{ $i }}" required
                                        class="sr-only">
                                    {{ $i }}
                                </label>
                            @endfor
                        </div>
                        <textarea name="comment" rows="2" placeholder="Komentar (opsional)"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm placeholder:text-slate-400"></textarea>
                        <button
                            class="btn-press rounded-xl bg-navy-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-navy-800">Kirim
                            penilaian</button>
                    </form>
                </x-card>
            @endif
        </div>

        <div class="min-w-0 space-y-4">
            <x-card title="Tindakan">
                <div class="space-y-2.5">
                    @can('update', $complaint)
                        <a href="{{ route('complaints.edit', $complaint) }}"
                            class="btn-press block rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Ubah
                            laporan</a>
                    @endcan
                    @can('delete', $complaint)
                        <form method="POST" action="{{ route('complaints.destroy', $complaint) }}">@csrf @method('DELETE')<button
                                data-confirm="Hapus laporan {{ $complaint->ticket_number }}? Tindakan ini tidak dapat dibatalkan."
                                class="btn-press w-full rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">Hapus
                                laporan</button></form>
                    @endcan
                    @can('handle', $complaint)
                        <form method="POST" action="{{ route('complaints.accept', $complaint) }}">@csrf<button
                                class="btn-press w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Terima
                                penugasan</button></form>
                        <form method="POST" action="{{ route('complaints.complete', $complaint) }}"
                            class="space-y-2.5 border-t border-slate-100 pt-3.5">
                            @csrf
                            <textarea name="summary" rows="2" required placeholder="Ringkasan penyelesaian…"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm placeholder:text-slate-400"></textarea>
                            <button
                                class="btn-press w-full rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_12px_24px_-12px_rgba(4,120,87,0.7)] hover:bg-emerald-600">Ajukan
                                selesai</button>
                        </form>
                    @endcan
                    @can('verify', $complaint)
                        <form method="POST" action="{{ route('complaints.verify', $complaint) }}" class="space-y-2.5">
                            @csrf
                            <input name="verification_note" placeholder="Catatan verifikasi (opsional)"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm placeholder:text-slate-400">
                            <button
                                class="btn-press w-full rounded-xl bg-navy-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-navy-800">Verifikasi</button>
                        </form>
                        <form method="POST" action="{{ route('complaints.reject', $complaint) }}" class="space-y-2.5">
                            @csrf
                            <textarea name="rejection_reason" rows="2" required minlength="10" placeholder="Alasan penolakan (wajib)…"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm placeholder:text-slate-400"></textarea>
                            <button data-confirm="Tolak laporan ini?"
                                class="btn-press w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-500">Tolak
                                laporan</button>
                        </form>
                    @endcan
                    @can('assign', $complaint)
                        <form method="POST" action="{{ route('complaints.assign', $complaint) }}"
                            class="space-y-2.5 border-t border-slate-100 pt-3.5">
                            @csrf
                            <select name="assigned_to" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm">
                                <option value="">— Pilih koordinator —</option>
                                @foreach (\App\Models\User::whereIn('role', \App\Models\User::KOORDINATOR_ROLES)->where('is_active', true)->orderBy('name')->get() as $p)
                                    <option value="{{ $p->id }}" @selected((int) $complaint->assigned_to === (int) $p->id)>{{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                            <textarea name="instruction" rows="2" required minlength="10" placeholder="Instruksi penanganan…"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm placeholder:text-slate-400"></textarea>
                            <button
                                class="btn-press w-full rounded-xl bg-navy-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-navy-800">Tugaskan</button>
                        </form>
                    @endcan
                    @if (auth()->user()->isRole('admin', 'pimpinan') && $complaint->status !== 'selesai' && $complaint->status !== 'ditutup')
                        <form method="POST" action="{{ route('complaints.status', $complaint) }}"
                            class="space-y-2.5 border-t border-slate-100 pt-3.5">
                            @csrf
                            <div class="grid grid-cols-2 gap-2">
                                <select name="to_status" required
                                    class="rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm">
                                    @foreach (\App\Models\Complaint::TRANSITIONS[$complaint->status] ?? [] as $next)
                                        <option value="{{ $next }}">
                                            {{ \App\Models\Complaint::STATUS_LABELS[$next] ?? $next }}</option>
                                    @endforeach
                                </select>
                                <input name="note" placeholder="Catatan"
                                    class="rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm placeholder:text-slate-400">
                            </div>
                            <button
                                class="btn-press w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Ubah
                                status</button>
                        </form>
                    @endif
                </div>
            </x-card>

            <x-card title="Riwayat status">
                @forelse($complaint->statusHistories as $h)
                    <div class="border-b border-slate-50 py-2.5 text-xs last:border-0">
                        <p class="font-semibold text-slate-700">{{ $h->from_status ?? '—' }} → {{ $h->to_status }}</p>
                        <p class="mt-0.5 tabular-nums text-slate-400">{{ $h->changer?->name ?? 'Sistem' }} ·
                            {{ $h->created_at->format('d M Y H:i') }}</p>
                        @if (
                            $h->note &&
                                (auth()->user()->isRole('admin', 'pimpinan') || auth()->user()->isKoordinator() || (int) $complaint->user_id === (int) auth()->id()))
                            <p class="mt-1 leading-relaxed text-slate-600">{{ $h->note }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada riwayat.</p>
                @endforelse
            </x-card>

            @if ($complaint->approvals->isNotEmpty())
                <x-card title="Persetujuan">
                    @foreach ($complaint->approvals as $a)
                        <div class="border-b border-slate-50 py-2.5 text-xs last:border-0">
                            <p class="font-semibold text-slate-700">{{ ucfirst($a->type) }} — {{ ucfirst($a->status) }}
                            </p>
                            <p class="mt-0.5 text-slate-400">{{ $a->requester?->name }} ·
                                {{ $a->created_at->format('d M Y H:i') }}</p>
                            @if ($a->decision_note)
                                <p class="mt-1 leading-relaxed text-slate-600">Keputusan: {{ $a->decision_note }}</p>
                            @endif
                        </div>
                    @endforeach
                </x-card>
            @endif

            @if (auth()->user()->isRole('admin') && $complaint->isSensitive())
                <x-card title="Kelola akses sensitif">
                    <form method="POST" action="{{ route('complaints.grant-access', $complaint) }}"
                        class="space-y-2.5">
                        @csrf
                        <select name="user_id" required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm">
                            <option value="">— Pilih admin/pimpinan —</option>
                            @foreach (\App\Models\User::whereIn('role', ['admin', 'pimpinan'])->where('is_active', true)->orderBy('name')->get() as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->roleLabel() }})</option>
                            @endforeach
                        </select>
                        <input name="reason" required minlength="5" placeholder="Alasan pemberian akses"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm placeholder:text-slate-400">
                        <button
                            class="btn-press w-full rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-500">Beri
                            akses</button>
                    </form>
                    @if ($complaint->accesses->isNotEmpty())
                        <ul class="mt-3 space-y-1.5 text-xs text-slate-600">
                            @foreach ($complaint->accesses as $acc)
                                <li class="rounded-lg bg-amber-50/60 px-2.5 py-1.5 ring-1 ring-amber-900/10">
                                    {{ $acc->user?->name }} — {{ $acc->reason }}</li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>
            @endif
        </div>
    </div>
@endsection
