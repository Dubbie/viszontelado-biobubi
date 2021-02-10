<?php

namespace App\Subesz;

class MoneyService
{
    /**
     * @param $value
     * @return string
     */
    public function getFormattedMoney($value): string
    {
        $money = ceil($value);

        return number_format($money, 0, '.', ' ');
    }
}
