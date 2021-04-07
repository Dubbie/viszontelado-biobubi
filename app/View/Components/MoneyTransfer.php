<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MoneyTransfer extends Component
{
    /** @var \App\MoneyTransfer */
    private $transfer;

    /**
     * Create a new component instance.
     *
     * @param  \App\MoneyTransfer  $moneyTransfer
     */
    public function __construct(\App\MoneyTransfer $moneyTransfer) {
        $this->transfer = $moneyTransfer;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render() {
        return view('components.money-transfer')->with([
            'transfer' => $this->transfer,
        ]);
    }
}
