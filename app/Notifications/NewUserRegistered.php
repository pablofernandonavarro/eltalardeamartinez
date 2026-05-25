<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserRegistered extends Notification
{
    public function __construct(public readonly User $newUser) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $unit = $this->newUser->requestedUnit?->full_identifier ?? 'sin unidad';

        return (new MailMessage)
            ->subject('Nuevo usuario registrado - El Talar de Martínez')
            ->greeting('Hola, '.$notifiable->name.'.')
            ->line('Un nuevo usuario se registró y está esperando aprobación.')
            ->line('**Nombre:** '.$this->newUser->name)
            ->line('**Email:** '.$this->newUser->email)
            ->line('**Unidad solicitada:** '.$unit)
            ->action('Revisar en el panel', route('admin.users.index'))
            ->salutation('El sistema de El Talar de Martínez');
    }
}
