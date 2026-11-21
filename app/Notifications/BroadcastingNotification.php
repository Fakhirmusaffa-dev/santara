<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi database yang juga disiarkan realtime via Reverb.
 * Dipakai bersama notifikasi database lain dengan bungkus sederhana,
 * sehingga tiap pemberitahuan langsung muncul tanpa reload.
 */
class BroadcastingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Notification $notification,
        public object $notifiable,
    ) {}

    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.'.$this->notifiable->getKey().'.notifications')];
    }

    public function broadcastWith(): array
    {
        return $this->notification->toArray($this->notifiable);
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->notification->toArray($notifiable));
    }
}
