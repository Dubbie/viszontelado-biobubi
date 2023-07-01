<?php

namespace App\Subesz;

use Carbon\Carbon;

class GlsService
{
    public function getParcelList(Carbon $from, Carbon $to) {
        //Service calling:
        $pickupDateFrom="\/Date(".($from->timestamp * 1000).")\/";
        $pickupDateTo="\/Date(".($to->timestamp * 1000).")\/";
        $username = config('app.gls.username');
        $password = config('app.gls.password');
        $passwordHashed = "[".implode(',',unpack('C*', hash('sha512', $password, true)))."]";
        $request = sprintf('{"Username":"%s","Password":'.$passwordHashed.',"PickupDateFrom":"%s","PickupDateTo":"%s","PrintDateFrom":null,"PrintDateTo":null}', $username, $pickupDateFrom, $pickupDateTo);

        return json_decode($this->getResponse($this->getParcelServiceUrl('GetParcelList'), $request), true);
    }

    private function getParcelServiceUrl($function = null) {
        $baseUrl = config('app.gls.api_url');
        if ($function) {
            $baseUrl .= $function;
        }
        return str_replace('SERVICE_NAME', 'ParcelService', $baseUrl);
    }

    private function getResponse($url, $request) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($request))
        );

        $response = curl_exec($curl);
        if(curl_getinfo($curl)["http_code"] == "401")
        {
            die("Unauthorized");
        }

        if ($response === false)
        {
            die('curl_error:"' . curl_error($curl) . '";curl_errno:' . curl_errno($curl));
        }
        curl_close($curl);
        return $response;
    }
}