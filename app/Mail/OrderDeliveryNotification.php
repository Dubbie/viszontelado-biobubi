<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderDeliveryNotification extends Mailable
{
    use Queueable, SerializesModels;

    private string $message;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.orders.delivery-notification')->subject('BioBubi holnapi kiszállítás')->with([
            'message' => $this->message
        ]);
    }
}
