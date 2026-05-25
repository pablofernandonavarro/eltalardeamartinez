<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserApproved extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu cuenta fue aprobada - El Talar de Martínez')
            ->greeting('Hola, '.$notifiable->name.'.')
            ->line('Tu cuenta en El Talar de Martínez fue aprobada. Ya podés acceder al sistema.')
            ->action('Ingresar al sistema', route('login'))
            ->line('Si no solicitaste esta cuenta, podés ignorar este mensaje.')
            ->salutation('El equipo de El Talar de Martínez');
    }
}
