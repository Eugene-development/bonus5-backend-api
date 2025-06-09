<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailRussian extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

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
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Подтвердите ваш адрес электронной почты - BONUS5')
            ->greeting('Добро пожаловать в BONUS5!')
            ->line('Спасибо за регистрацию в нашем сервисе. Для завершения регистрации необходимо подтвердить ваш адрес электронной почты.')
            ->line('Нажмите на кнопку ниже, чтобы подтвердить ваш email адрес:')
            ->action('Подтвердить адрес электронной почты', $verificationUrl)
            ->line('Если кнопка не работает, скопируйте и вставьте эту ссылку в адресную строку браузера:')
            ->line($verificationUrl)
            ->line('Если вы не регистрировались на нашем сайте, просто проигнорируйте это письмо.')
            ->salutation('С уважением, команда BONUS5');
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify.web',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
