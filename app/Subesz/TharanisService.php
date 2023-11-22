<?php

namespace App\Subesz;

use App\Order;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SoapClient;
use SoapFault;

class TharanisService
{
	private SoapClient $client;
	private string $customerCode;
	private string $companyCode;
	private string $apiKey;

	public function __construct()
	{
		$this->customerCode = config('tharanis.customerCode');
		$this->companyCode = config('tharanis.companyCode');
		$this->apiKey = config('tharanis.apiKey');

		try {
			$this->client = new SoapClient(null, [
				'location' => 'https://login.tharanis.hu/apiv3.php',
				'uri'      => 'urn://apiv3',
			]);
		} catch (SoapFault $e) {
			Log::error('Hiba a Tharanis kliens létrehozásakor.');
			Log::error('- Hiba: ');
			Log::error($e->getMessage());
		}
	}

	public function createInvoice($order)
	{
		$os = resolve('App\Subesz\OrderService');
		/** @var \App\Order $localOrder */
		$localOrder = $os->getLocalOrderByResourceId($order['order']->id);
		$response = [
			'success' => false,
			'message' => 'Számla létrehozásának inicalizálása',
		];

		// Az adatokat lekérjük 1 funkcióval
		$data = $this->getInvoiceData($order, $localOrder);
		if ($data === null) {
			$response['message'] = "Hiba történt a számla adatainak átalakításakor. Nem tudtuk létrehozni.";
			return $response;
		}

		foreach ($order['products']->items as $product) {
			$productData = [
				'raktar' => '1',
				'cikksz' => $product->sku,
				'netto_ar' => $product->price,
				'menny' => $product->stock1,
			];

			$data['tetelek']['tetel'][] = $productData;
		}

		foreach ($order['totals'] as $total) {
			if ($total->type == 'COUPON') {
				$data['tetelek']['tetel'][] = [
					'raktar' => '1',
					'cikksz' => 'kupon2',
					'netto_ar' => strval($total->value / 1.27),
					'menny' => '1',
				];
			} else {
				if ($total->type == 'SHIPPING' && intval($total->value) > 0) {
					$data['tetelek']['tetel'][] = [
						'raktar' => '1',
						'cikksz' => 'glsszk',
						'netto_ar' => strval($total->value / 1.27),
						'menny' => '1',
					];
				}
				if ($total->type == 'LOYALTYPOINTS_TO_USE' && intval($total->value) < 0) {
					$data['tetelek']['tetel'][] = [
						'raktar' => '1',
						'cikksz' => 'Huseg2',
						'netto_ar' => strval($total->value / 1.27),
						'menny' => '1',
					];
				}
				if ($total->type == 'PAYMENT') {
					$data['tetelek']['tetel'][] = [
						'raktar' => '1',
						'cikksz' => 'utanvet',
						'netto_ar' => strval($total->value / 1.27),
						'menny' => '1',
					];
				}
			}
		}

		$xml = ArrayToXml::convert($data, [
			'rootElementName' => 'szamla',
			'_attributes' => [
				'valasz' => 1
			],
		]);

		$result = $this->berak('kimeno_szamla', $xml);

		if ($result['hiba'] != "0") {
			Log::error('Hiba történt a Tharanis számla létrehozásakor!');
			Log::error($result['valasz']);

			$response['message'] = $result['valasz'];
			return $response;
		}

		Log::info("A Tharanis számla létrejött: " . $result['valasz']['sorszam']);

		// Nincs hiba, mentsük el a számlát.
		$path = $this->saveInvoiceByEncodedPDF($localOrder, $result['valasz']['pdf']);
		if ($path) {
			$localOrder->invoice_id = $result['valasz']['sorszam'];
			$localOrder->invoice_path = $path;
			$localOrder->save();
			Log::info(sprintf("Számla hozzárendelve a megrendeléshez (ID: %s, Számla: %s)", $localOrder->id, $path));
			$response['message'] = "Számla sikeresen létrejött és hozzá lett rendelve a megrendeléshez";
		}

		$response['success'] = true;
		return $response;
	}

	public function test()
	{
		$data = [
			'szurok' => [
				'szuro' => [
					'mezo' => 'cikksz',
					'relacio' => [
						'_cdata' => '='
					],
					'ertek' => 'BBABL|BBVO|Huseg2|glsszk|kupon2|utanvet',
				],
			],
		];

		$xml = ArrayToXml::convert($data, 'leker', true, 'UTF-8');
		return $this->leker('cikk', $xml);
	}

	public function saveInvoiceByEncodedPDF($localOrder, $pdfData): bool|string
	{
		$fname = 'ssz-tharanis-szamla-' . date('Ymd_His') . '.pdf';
		$path  = sprintf('invoices/%s/%s', $localOrder->id, $fname);

		if (Storage::put($path, base64_decode($pdfData))) {
			Log::info(sprintf('Számla sikeresen elmentve (Fájl: %s)', $path));

			return $path;
		} else {
			Log::info('Hiba történt a számla elmentésekor a rendszerbe!');

			return false;
		}
	}

	private function getInvoiceData(array $order, Order $localOrder): ?array
	{
		$paymentMethod = $this->getTharanisPaymentMethodByName($localOrder);
		if ($paymentMethod == "") {
			Log::error('Hiba a kifizetési mód eldöntésekor a TharanisService-ben. A számlát nem tudjuk legenerálni.');
			$response['message'] = "Hiba a kifizetési mód eldöntésekor a TharanisService-ben. A számlát nem tudjuk legenerálni.";
			return $response;
		}

		$data = [
			'fej' => [
				'technikai' => false,
				'eszla' => true,
				'teljdat' => Carbon::now()->format('Y.m.d'),
				'fizhat' => Carbon::now()->format('Y.m.d'),
				'arfolyam' => 1,
				'valuta' => 'HUF',
				'uzletag' => 'A',
				//'shop' => 'biobubi.hu',
				'email' => $order['order']->email,
				'telefon' => $order['order']->phone,
				'fiz_mod' => $paymentMethod,
				'megjegyzes' => $order['order']->comment,
			],
			'tetelek' => [
				'tetel' => []
			]
		];

		// Hozzáadjuk a partner adatait
		$partnerData = $this->getPartnerData($order, $localOrder);

		if (!$partnerData) {
			return null;
		}

		$headData = array_merge($data['fej'], $partnerData);
		$data['fej'] = $headData;

		return $data;
	}

	private function getPartnerData(array $order, Order $localOrder): ?array
	{
		$partnerData = [];

		// Céges esetén létre kell hoznunk egy új partnert
		if (strlen($order['order']->taxNumber) > 0 && strlen($order['order']->paymentCompany) > 0) {
			$partnerResponse = $this->createPartner($order['order'], $localOrder);

			try {
				Log::info("-----------------------------------------------------------");
				Log::info("Partner response");
				Log::info($partnerResponse);
				Log::info("-----------------------------------------------------------");
			} catch (Exception $e) {
				Log::error("Hiba a partner response kiiratásakor");
			}

			// Eldöntjük, hogy létrejött-e a partner, ha igen akkor létezik az 'elem' kulcs
			if ($partnerResponse['elem']['hiba'] == 1) {
				Log::error("Hiba történt a Tharanis partner létrehozásakor!");
				Log::error($partnerResponse['elem']['valasz']);

				return null;
			} else {
				$partnerData['szla_partkod'] = $partnerResponse['elem']['valasz']['azon'];
			}
		} else {
			$partnerData = [
				'szla_partkod' => '',
				'szla_nev' => sprintf('%s %s', $order['order']->firstname, $order['order']->lastname),
				'szla_orszag' => $order['order']->paymentCountryName, // TODO: Ezt ISO3-ra kéne alakítanunk
				'szla_irsz' => $order['order']->paymentPostcode,
				'szla_telepul' => $order['order']->paymentCity,
				'szla_utca' => trim(sprintf('%s %s', $order['order']->paymentAddress1, $order['order']->paymentAddress2)),
			];
		}

		// Szállítási adatok felülírása
		$partnerData['szall_mod'] = $this->getTharanisShippingMethodByName($order['order']->shippingMethodLocalizedName);
		$partnerData['szall_nev'] = sprintf('%s %s', $order['order']->shippingFirstname, $order['order']->shippingLastname);
		$partnerData['szall_orszag'] = $order['order']->shippingCountryName;
		$partnerData['szall_irsz'] = $order['order']->shippingPostcode;
		$partnerData['szall_telepul'] = $order['order']->shippingCity;
		$partnerData['szall_utca'] = trim(sprintf('%s %s', $order['order']->shippingAddress1, $order['order']->shippingAddress2));

		return $partnerData;
	}

	private function createPartner(object $order, Order $localOrder): array
	{
		$data = [
			'partner' => [
				'megnev' => $order->paymentCompany,
				'cim' => [
					'szekhely' => [
						'orszag' => $order->paymentCountryName,
						'irszam' => $order->paymentPostcode,
						'telepul' => $order->paymentCity,
						'utca' => trim(sprintf('%s %s', $order->paymentAddress1, $order->paymentAddress2))
					],
					'szall' => [
						'orszag' => $order->shippingCountryName,
						'irszam' => $order->shippingPostcode,
						'telepul' => $order->shippingCity,
						'utca' => trim(sprintf('%s %s', $order->shippingAddress1, $order->shippingAddress2))
					]
				],
				'telefon' => $order->phone,
				'email' => $order->email,
				'adoszam' => $order->taxNumber,
				// 'fizmod' => $this->getTharanisPaymentMethodByName($localOrder),
				'fizmod' => 'Utánvét',
				'tipus' => 'V',
			]
		];

		// Partner létrehozása thanarisba
		$xml = ArrayToXml::convert($data, [
			'rootElementName' => 'partnerek',
			'_attributes' => [
				'valasz' => 1
			],
		]);

		try {
			$result = $this->berak('partner', $xml);

			return $result;
		} catch (Exception $e) {
			Log::error("Váratlan hiba történt a Tharanis partner létrehozásakor! (Céges megrendelés)");
			Log::error($e->getMessage());

			return null;
		}
	}

	private function leker($function, $xml = null)
	{
		$data = $this->client->leker($this->customerCode, $this->companyCode, $this->apiKey, $function, $xml);
		$xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_NOCDATA);
		return json_decode(json_encode($xml), TRUE);
	}

	private function berak($function, $xml = null)
	{
		$data = $this->client->berak($this->customerCode, $this->companyCode, $this->apiKey, $function, $xml);
		$xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_NOCDATA);
		return json_decode(json_encode($xml), TRUE);
	}

	private function getTharanisPaymentMethodByName($localOrder): string
	{
		$paymentMethod = '';

		// Döntsük el, mi lett a végleges fizetési mód
		if ($localOrder->final_payment_method == 'Készpénz') {
			$paymentMethod = 'Utánvét';
			$this->logInfo('A kifizetés módja: "Készpénz" volt, de nem használják ezért "Utánvét" kerül rá.');
		} elseif ($localOrder->final_payment_method == 'Bankkártya') {
			$paymentMethod = 'Utánvét';
			$this->logInfo('A kifizetés módja: "Bankkártya" volt, de nem használják ezért "Utánvét" kerül rá.');
		} elseif ($localOrder->final_payment_method == 'Átutalás') {
			$paymentMethod = 'Átutalás';
			$this->logInfo('A kifizetés módja: "Átutalás" volt');
		} elseif ($localOrder->final_payment_method == 'Online Bankkártya') {
			$paymentMethod = 'Bankkártya Web';
			$this->logInfo('A kifizetés módja: "Bankkártya Web" volt');
		} else {
			$this->logError('Hiba a kifizetés módjával: Olyan kifizetés mód ami nem kezelt... ' . $localOrder->final_payment_method);
		}

		return $paymentMethod;
	}

	private function getTharanisShippingMethodByName($shippingMethod): string
	{
		return match ($shippingMethod) {
			"Biobubi futá" => "Biobubi futár",
			"FoxPost Csomagautomata" => "FoxPost Csomagautomata",
			"Gls házhozszállítás" => "GLS",
			"GLS Csomagautomata" => "GLS Csomagpont",
			"Gls házhozszállítás flakonvisszaküldéssel" => "Gls házhozszállítás flakonvisszaküldéssel",
			"Országos hulldékmentes szállítás" => "Trans-Sped",
			"Országos házhozszállítás" => "Trans-Sped",
			"Biobubi futár" => "Trans-Sped",
			default => $shippingMethod,
		};
	}

	private function convertToXml($data, $rootElement = 'leker')
	{
		return ArrayToXml::convert($data, $rootElement, true, 'UTF-8');
	}

	private function logInfo($msg)
	{
		Log::info($this->prefixMessage($msg));
	}

	private function logError($msg)
	{
		Log::error($this->prefixMessage($msg));
	}

	private function prefixMessage($msg)
	{
		return sprintf('[TharanisService] %s', $msg);
	}
}
