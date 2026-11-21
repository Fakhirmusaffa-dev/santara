<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GenericAnnouncement extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
        public ?string $ticketNumber = null,
    ) {}

    public function via(object $notifiable): array
    {
        // Cukup kanal database. Siaran realtime sengaja tidak dipakai: event
        // broadcast notifikasi tidak membawa kanal penerima yang benar untuk
        // notifikasi berbungkus, sehingga berisiko error pada jalur publik.
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'ticket_number' => $this->ticketNumber,
            'url' => $this->url,
        ];
    }
}
