<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountApprovedNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $roleLabel = $notifiable->role === 'ADMIN' ? 'Administrator' : 'Staff Operasional';

        return (new MailMessage)
            ->subject('Akun Anda Telah Disetujui (ACC) - ARTDEVATA Guide Management')
            ->greeting("Selamat, {$notifiable->name}!")
            ->line("Kabar baik! Pendaftaran akun Anda sebagai **{$roleLabel}** telah disetujui (ACC) oleh Administrator.")
            ->line("Akun Anda kini telah AKTIF sepenuhnya. Anda sudah dapat masuk dan menggunakan seluruh fitur operasional sesuai hak akses Anda.")
            ->action('Masuk ke Sistem Sekarang', route('login'))
            ->line("Terima kasih telah bergabung bersama ARTDEVATA Bali.");
    }
}
