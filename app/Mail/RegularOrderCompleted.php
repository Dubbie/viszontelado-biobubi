<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegularOrderCompleted extends Mailable
{
    use Queueable, SerializesModels;

    private $order;
    private $invoicePath;

    /**
     * Create a new message instance.
     *
     * @param $order
     * @param $invoicePath
     */
    public function __construct($order, $invoicePath)
    {
        $this->order = $order;
        $this->invoicePath = $invoicePath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.orders.regular-completed')->with([
            'order' => $this->order,
        ])->attach(str_replace('\\', '/', storage_path('app/' . $this->invoicePath)));
    }
}
