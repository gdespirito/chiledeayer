<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class PhotoCommentedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public int $photoId,
        public string $commenterName,
        public string $commentBody,
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
            ->subject('Nuevo comentario en tu foto')
            ->greeting('Hola '.$notifiable->name.'!')
            ->line($this->commenterName.' comentó en tu foto:')
            ->line('"'.Str::limit($this->commentBody, 100).'"')
            ->action('Ver foto', url(route('photos.show', $this->photoId)))
            ->line('Gracias por ser parte de Chile de Ayer.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'photo_commented',
            'photo_id' => $this->photoId,
            'commenter_name' => $this->commenterName,
            'comment_body_excerpt' => Str::limit($this->commentBody, 50),
        ];
    }
}
