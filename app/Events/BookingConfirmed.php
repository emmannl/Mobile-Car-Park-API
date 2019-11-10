<?php

namespace App\Events;

use App\CarParkBooking;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Booking confirmed event
 * Class BookingConfirmed
 * @package App\Events
 */
class BookingConfirmed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var CarParkBooking
     */
    public $booking;
    /**
     * @var User
     */
    public $user;
    /**
     * @var \App\User
     */
    public $parkAdmin;
    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param CarParkBooking $booking
     */
    public function __construct(User $user, CarParkBooking $booking)
    {
        $this->user = $user;
        $this->booking = $booking;
        $this->parkAdmin = $this->getCarParkAdmin();
    }

    /**
     * Get the admin of the car park being booked
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    private function getCarParkAdmin()
    {
        return User::query()->where('id', function ($query) {
           return $query->from('car_parks')->where('id', $this->booking->car_park_id)->select('user_id')->first();
        })->first();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
