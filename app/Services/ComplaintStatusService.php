<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ComplaintStatusHistory;
use App\Models\User;
use App\Notifications\ComplaintStatusChanged;
use App\Support\Notify;
use Illuminate\Support\Facades\DB;

class ComplaintStatusService
{
    /**
     * Ubah status dengan kontrol transisi + riwayat + notifikasi aman.
     *
     * @throws \InvalidArgumentException
     */
    public function change(Complaint $complaint, string $to, User $actor, ?string $note = null): Complaint
    {
        if (! $complaint->canTransitionTo($to)) {
            throw new \InvalidArgumentException(
                "Transisi status dari {$complaint->status} ke {$to} tidak diizinkan."
            );
        }

        return DB::transaction(function () use ($complaint, $to, $actor, $note) {
            $from = $complaint->status;

            $complaint->status = $to;

            if ($to === 'selesai') {
                $complaint->resolved_at ??= now();
            }
            if ($to === 'ditutup') {
                $complaint->closed_at ??= now();
            }

            $complaint->save();

            ComplaintStatusHistory::create([
                'complaint_id' => $complaint->id,
                'from_status' => $from,
                'to_status' => $to,
                'changed_by' => $actor->id,
                'note' => $note,
            ]);

            ActivityLogger::log(
                action: 'status_changed',
                description: "Tiket {$complaint->ticket_number}: {$from} -> {$to}",
                subjectType: Complaint::class,
                subjectId: $complaint->id,
                metadata: ['from' => $from, 'to' => $to, 'actor_id' => $actor->id]
            );

            $this->notify($complaint, $from, $to);

            return $complaint->fresh();
        });
    }

    protected function notify(Complaint $complaint, string $from, string $to): void
    {
        $targets = collect([$complaint->reporter, $complaint->assignee])->filter()->unique('id');

        foreach ($targets as $target) {
            // Jangan kirim narasi sensitif — hanya tiket + status.
            Notify::send(
                $target,
                new ComplaintStatusChanged($complaint->ticket_number, $from, $to),
            );
        }
    }
}
