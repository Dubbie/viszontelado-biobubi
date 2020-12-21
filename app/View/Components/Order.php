<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Order extends Component
{
    /** @var \App\Order */
    public $order;

    /**
     * Create a new component instance.
     *
     * @param \App\Order $order
     */
    public function __construct(\App\Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.order');
    }
}
