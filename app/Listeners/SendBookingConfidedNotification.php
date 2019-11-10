<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;
use App\Notifications\InformParkAdminOfBooking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendBookingConfidedNotification
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
     * @param  BookingConfirmed  $event
     * @return void
     */
    public function handle(BookingConfirmed $event)
    {
        $event->user->notify(new \App\Notifications\BookingConfirmed($event->booking));

        $event->parkAdmin->notify(new InformParkAdminOfBooking($event->parkAdmin, $event->booking));
    }
}
