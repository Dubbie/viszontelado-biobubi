<?php

namespace App\Subesz;

class MoneyService
{
    /**
     * @param $value
     * @return string
     */
    public function getFormattedMoney($value): string {
        $money = round($value);

        return number_format($money, 0, '.', ' ');
    }
}
