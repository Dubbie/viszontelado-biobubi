<?php

namespace App\Subesz;

use Carbon\Carbon;

class GlsService
{
	/**
	 * Check if the API works.
	 *
	 * @return bool
	 */
	public function isApiWorking()
	{
		$start = Carbon::now()->subHour();
		$end = Carbon::now();
		$response = $this->getParcelList($start, $end);

		return array_key_exists('PrintDataInfoList', $response);
	}

	public function getParcelList(Carbon $from, Carbon $to)
	{
		//Service calling:
		$pickupDateFrom = "\/Date(" . ($from->timestamp * 1000) . ")\/";
		$pickupDateTo = "\/Date(" . ($to->timestamp * 1000) . ")\/";
		$username = config('app.gls.username');
		$password = config('app.gls.password');
		$passwordHashed = "[" . implode(',', unpack('C*', hash('sha512', $password, true))) . "]";
		$request = sprintf('{"Username":"%s","Password":' . $passwordHashed . ',"PickupDateFrom":"%s","PickupDateTo":"%s","PrintDateFrom":null,"PrintDateTo":null}', $username, $pickupDateFrom, $pickupDateTo);

		return json_decode($this->getResponse($this->getParcelServiceUrl('GetParcelList'), $request), true);
	}

	private function getParcelServiceUrl($function = null)
	{
		$baseUrl = config('app.gls.api_url');
		if ($function) {
			$baseUrl .= $function;
		}
		return str_replace('SERVICE_NAME', 'ParcelService', $baseUrl);
	}

	private function getResponse($url, $request)
	{
		$options = array(
			CURLOPT_POST => 1,
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_POSTFIELDS => $request,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($request)
			)
		);

		$curl = curl_init();
		curl_setopt_array($curl, $options);

		$response = curl_exec($curl);
		$error = curl_error($curl);
		curl_close($curl);

		if ($error) {
			die('curl_error:"' . $error . '";curl_errno:' . curl_errno($curl));
		}

		if (curl_getinfo($curl)["http_code"] == "401") {
			die("Unauthorized");
		}

		return $response;
	}
}
