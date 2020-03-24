<?php

namespace App\Subesz;


class PhoneService
{
    public function getFormattedPhoneNumber($value) {
        $value =  preg_replace("/[^0-9]/", "", $value);

        if (strlen($value) != 11) {
            // Lehagyta a prefixet??
            if (strlen($value) == 9 || strlen($value) == 8) {
                $value = "06$value";
            }
        }

        // Nézzük meg, hogy budapesti a szám avagy sem
        $from_budapest = strlen($value) == 10;

        // Kiszedjük belőle az információkat
        $domestic_code = substr($value, 0, 2);
        $area_code = $from_budapest ? substr($value, 2, 1) : substr($value, 2, 2);
        $first_part = $from_budapest ? substr($value, 3, 3) : substr($value, 4, 3);
        $second_part = $from_budapest ? substr($value, 6, strlen($value)) : substr($value, 7, strlen($value));

        // +36
        $domestic_code = $domestic_code == '36' ? '06' : $domestic_code;

        $formatted = sprintf(
            '%s %s %s %s',
            $domestic_code,
            $area_code,
            $first_part,
            $second_part
        );

        return $formatted;
    }
}