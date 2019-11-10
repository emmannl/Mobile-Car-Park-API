<?php

namespace App\Notifications;

use App\CarParkBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmed extends Notification
{
    use Queueable, ResolveChannelsTrait;

    private $booking;
    private $title;
    private $data;
    /**
     * Create a new notification instance.
     *
     * @param CarParkBooking $booking
     */
    public function __construct(CarParkBooking $booking)
    {
        $this->booking = $booking;
        $this->title = "Booking #{$booking->id} has been confirmed. Pay up to secure the space";
        $this->data = $booking->toArray();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $this->resolveChannel($notifiable);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'data' => $this->data,
        ];
    }
}
