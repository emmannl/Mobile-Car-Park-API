<?php

namespace App\Listeners;

use App\Events\PaymentVerified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPaymentCompletionNotification
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
     * @param  PaymentVerified  $event
     * @return void
     */
    public function handle(PaymentVerified $event)
    {
        //
    }
}
