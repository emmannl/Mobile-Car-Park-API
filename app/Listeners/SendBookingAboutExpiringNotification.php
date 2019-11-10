<?php

namespace App\Listeners;

use App\Events\ParkingSpaceBookingAboutExpriing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendBookingAboutExpiringNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ParkingSpaceBookingAboutExpriing  $event
     * @return void
     */
    public function handle(ParkingSpaceBookingAboutExpriing $event)
    {
        //
    }
}
