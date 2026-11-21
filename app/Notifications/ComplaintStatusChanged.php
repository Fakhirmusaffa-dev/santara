<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ComplaintStatusChanged extends Notification
{
    use Queueable;

    /**
     * Target siaran diisi oleh kanal broadcast Laravel saat mengirim.
     * Tidak ikut diserialisasi ke database (lihat __sleep).
     */
    public ?object $notifiable = null;

    public function __construct(
        public string $ticketNumber,
        public string $fromStatus,
        public string $toStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return config('broadcasting.default') === 'null'
            ? ['database']
            : ['database', 'broadcast'];
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . ($this->notifiable ?? auth()->user())->getKey() . '.notifications')];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Status laporan diperbarui',
            'message' => "Tiket {$this->ticketNumber} berubah dari {$this->fromStatus} menjadi {$this->toStatus}.",
            'ticket_number' => $this->ticketNumber,
            'url' => route('complaints.index', ['search' => $this->ticketNumber], false),
        ];
    }

    /**
     * Jangan sertakan target broadcast saat diserialisasi (database/queue).
     *
     * @return array<int,string>
     */
    public function __sleep(): array
    {
        return ['id', 'locale', 'ticketNumber', 'fromStatus', 'toStatus', 'connection', 'queue', 'delay', 'afterCommit'];
    }
}
