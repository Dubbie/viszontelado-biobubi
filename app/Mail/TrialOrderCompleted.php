<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialOrderCompleted extends Mailable
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
    public function __construct($order, $invoicePath) {
        $this->order       = $order;
        $this->invoicePath = $invoicePath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        \Log::info(storage_path('app/'.$this->invoicePath));

        return $this->markdown('emails.orders.trial-completed')->subject('BioBubi Számla')->with([
            'order' => $this->order,
        ])->attach(storage_path('app/'.$this->invoicePath));
    }
}
