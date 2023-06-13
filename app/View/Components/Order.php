<?php

namespace App\View\Components;

use App\Worksheet;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Class Order
 * @package App\View\Components
 * @mixin Order
 */
class Order extends Component
{
    /** @var \App\Order */
    public $order;

    /** @var string */
    public $type;

    /** @var Worksheet|null */
    public $worksheet;

    /**
     * Create a new component instance.
     *
     * @param  \App\Order  $order
     * @param $type
     * @param $worksheet
     */
    public function __construct(\App\Order $order, $type, $worksheet)
    {
        $this->order = $order;
        $this->type = $type;
        $this->worksheet = $worksheet;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        switch ($this->type) {
            case 'worksheet':
                return view('components.worksheet-order');
            case 'delivery-notification':
                return view('components.delivery-notification-order');
            case 'regular':
            default:
                return view('components.order');
        }
    }
}
