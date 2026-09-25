<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public ?string $reason = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $roleLabel = $notifiable->role === 'ADMIN' ? 'Administrator' : 'Staff Operasional';

        $mail = (new MailMessage)
            ->subject('Pemberitahuan Status Pendaftaran - ARTDEVATA Guide Management')
            ->greeting("Halo, {$notifiable->name}.")
            ->line("Mohon maaf, permohonan pendaftaran akun Anda sebagai **{$roleLabel}** di sistem ARTDEVATA belum dapat disetujui oleh Administrator.");

        if ($this->reason) {
            $mail->line("**Alasan:** {$this->reason}");
        }

        $mail->line("Jika Anda merasa ada kekeliruan atau ingin mengajukan konfirmasi, silakan hubungi tim Administrator kami.");

        return $mail;
    }
}
