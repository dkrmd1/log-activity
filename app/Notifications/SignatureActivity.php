<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Notifikasi aktivitas tanda tangan / persetujuan (disimpan di tabel notifications).
 * Sengaja tidak ShouldQueue: hosting cPanel biasanya tidak punya queue worker.
 */
class SignatureActivity extends Notification
{
    public function __construct(
        public string $title,
        public string $message,
        public string $url,                       // path internal, mis. /reports?bulan=9&tahun=2026
        public string $icon = 'tabler-signature',
        public string $color = 'primary',         // primary | success | danger | warning
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'icon' => $this->icon,
            'color' => $this->color,
        ];
    }
}
