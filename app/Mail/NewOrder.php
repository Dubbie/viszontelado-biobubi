<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewOrder extends Mailable
{
    use Queueable, SerializesModels;

    private $order;

    /** @var User */
    private $reseller;

    /**
     * Create a new message instance.
     *
     * @param $order
     * @param User $reseller
     */
    public function __construct($order, User $reseller)
    {
        $this->order = $order;
        $this->reseller = $reseller;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.orders.new')->subject('Új megrendelésed érkezett - Viszonteladó Portál')->with([
            'order' => $this->order,
            'reseller' => $this->reseller,
        ]);
    }
}
