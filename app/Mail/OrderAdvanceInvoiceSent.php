<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Log;

class OrderAdvanceInvoiceSent extends Mailable
{
    use Queueable, SerializesModels;

    private $order;

    private $advanceInvoicePath;

    /**
     * Create a new message instance.
     *
     * @param $order
     * @param $advanceInvoicePath
     */
    public function __construct($order, $advanceInvoicePath) {
        $this->order              = $order;
        $this->advanceInvoicePath = $advanceInvoicePath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        Log::info(storage_path('app/'.$this->advanceInvoicePath));

        return $this->markdown('emails.orders.advance')->subject('BioBubi Előlegszámla')->with([
            'order' => $this->order,
        ])->attach(storage_path('app/'.$this->advanceInvoicePath));
    }
}
