<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PersonTaggedOnPhotoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public int $photoId,
        public string $taggerName,
        public string $personName,
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
        return (new MailMessage)
            ->subject('Alguien fue etiquetado en tu foto')
            ->greeting('Hola '.$notifiable->name.'!')
            ->line($this->taggerName.' etiquetó a '.$this->personName.' en tu foto.')
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
            'type' => 'person_tagged',
            'photo_id' => $this->photoId,
            'tagger_name' => $this->taggerName,
            'person_name' => $this->personName,
        ];
    }
}
