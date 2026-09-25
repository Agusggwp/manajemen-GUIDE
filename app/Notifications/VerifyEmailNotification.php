<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the verification URL.
     */
    protected function verificationUrl(object $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $roleLabel = $notifiable->role === 'ADMIN' ? 'Administrator' : 'Staff Operasional';

        return (new MailMessage)
            ->subject('Verifikasi Alamat Email - ARTDEVATA Guide Management')
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Terima kasih telah melakukan pendaftaran sebagai **{$roleLabel}** di Sistem Manajemen Operasional Tour Guide ARTDEVATA Bali.")
            ->line("Silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda:")
            ->action('Verifikasi Email Saya', $verificationUrl)
            ->line("Setelah email berhasil diverifikasi, pendaftaran akun Anda akan ditinjau dan disetujui (ACC) oleh Administrator sebelum Anda dapat masuk ke dalam sistem.")
            ->line("Link verifikasi ini berlaku selama 60 menit.")
            ->line("Jika Anda merasa tidak pernah mendaftarkan akun di sistem ini, silakan abaikan email ini.");
    }
}
