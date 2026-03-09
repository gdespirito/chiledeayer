<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PhotoEditedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param  array<string, mixed>  $changes
     */
    public function __construct(
        public int $photoId,
        public string $editorName,
        public array $changes,
        public bool $sendMail = true,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($this->sendMail) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $changedFields = implode(', ', array_keys($this->changes));

        return (new MailMessage)
            ->subject('Tu foto fue editada')
            ->greeting('Hola '.$notifiable->name.'!')
            ->line($this->editorName.' editó los datos de tu foto.')
            ->line('Campos modificados: '.$changedFields)
            ->action('Ver foto', url(route('photos.show', $this->photoId)))
            ->line('Gracias por ser parte de Archivo de Chile.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'photo_edited',
            'photo_id' => $this->photoId,
            'editor_name' => $this->editorName,
            'changes' => implode(', ', array_keys($this->changes)),
        ];
    }
}
