<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Письмо для сброса пароля.
 *
 * Заменяет стандартное уведомление Laravel: у него английский текст и подпись
 * «Regards, Laravel». Текст здесь — часть интерфейса сайта, поэтому пишется
 * по-русски и с голосом «Челны подземки».
 */
class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $minutes = (int) config(
            'auth.passwords.'.config('auth.defaults.passwords').'.expire',
            60
        );

        return (new MailMessage)
            ->subject('Сброс пароля на сайте «Челны подземка»')
            ->greeting('Здравствуйте!')
            ->line('Вы запросили сброс пароля на сайте «Челны подземка».')
            ->action('Задать новый пароль', $url)
            ->line("Ссылка действует {$minutes} минут и срабатывает один раз.")
            ->line('Если вы не запрашивали сброс, просто проигнорируйте это письмо: пароль останется прежним, и никто не получит доступ к аккаунту.')
            ->salutation('С уважением, «Челны подземка»');
    }
}
