<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class BookingStatusChanged extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $booking;
    protected $status;
    protected $message;
    protected $title;
    public function __construct($booking, $status,$message,$title)
    {
        $this->booking = $booking;
        $this->status  = $status;
        $this->message = $message ?? 'Your booking status has changed.';
        $this->title = $title ?? 'booking';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [FcmChannel::class, 'database'];
    }


    public function toFcm($notifiable): FcmMessage
    {
        return (new FcmMessage(notification: new FcmNotification(
            title: $this->title,
            body: $this->message
        )))
            ->data([
                'booking_id' => (string) $this->booking->id,
                'status'     => (string) $this->status,
                'service'    => (string) ($this->booking->service->name ?? ''),
                'date'       => (string) $this->booking->booking_date
            ])
            ->custom([
                'android' => [
                    'notification' => [
                        'color' => '#0A0A0A',
                    ],
                    'fcm_options' => [
                        'analytics_label' => 'analytics',
                    ],
                ],
                'apns' => [
                    'fcm_options' => [
                        'analytics_label' => 'analytics',
                    ],
                ],
            ]);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title'=> $this->title,
            'booking_id' => $this->booking->id,
            'status' => $this->booking->status,
            'message' => $this->message,
            'service' => $this->booking->service->name ?? null,
            'date' => $this->booking->booking_date,
        ];
    }
}
