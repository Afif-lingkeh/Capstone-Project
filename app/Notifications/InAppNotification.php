<?php
// app/Notifications/InAppNotification.php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Notifikasi umum: selalu tersimpan in-app; email hanya jika $mail = true dan penerima punya email.
// Isi notifikasi sengaja ringkas: tidak pernah memuat isi cerita konseling atau pesan pribadi.
class InAppNotification extends Notification
{
    public function __construct(
        public string $title,
        public ?string $body = null,
        public ?string $url = null,
        public string $icon = 'bell',
        public string $tone = 'blue',
        public bool $mail = false,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->mail && $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'icon' => $this->icon,
            'tone' => $this->tone,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[PEDULI] '.$this->title)
            ->line($this->body ?? $this->title)
            ->action('Buka PEDULI', $this->url ?? url('/'));
    }
}
