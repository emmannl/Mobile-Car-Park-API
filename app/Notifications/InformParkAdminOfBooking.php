<?php

namespace App\Notifications;

use App\CarParkBooking;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InformParkAdminOfBooking extends Notification
{
    use Queueable, ResolveChannelsTrait;

    private $parkAdmin;
    private $title;
    private $booking;

    /**
     * Create a new notification instance.
     *
     * @param User $parkAdmin
     * @param CarParkBooking $booking
     */
    public function __construct(User $parkAdmin, CarParkBooking $booking)
    {
        $this->parkAdmin  = $parkAdmin;
        $this->title = "A space in your parking lot has been booked";
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return  $this->resolveChannel($notifiable);
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
            'data' => $this->booking->toArray(),
        ];
    }
}
