<?php

namespace App\Subesz;


class MoneyService
{
    /**
     * @param $value
     * @return string
     */
    public function getFormattedMoney($value) {
        $money = intval($value);

        return number_format($money, 0, '.', '.');
    }
}