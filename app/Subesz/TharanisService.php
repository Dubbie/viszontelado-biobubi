<?php

namespace App\Subesz;

use Exception;
use Illuminate\Support\Carbon;
use Log;
use SoapClient;
use SoapFault;

class TharanisService
{
    private SoapClient $client;
    private string $customerCode;
    private string $companyCode;
    private string $apiKey;

    public function __construct() {
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

    public function createInvoice($order) {
        $data = [
            'fej' => [
                'technikai' => false, //  TODO: Ellenőrizzük
                'eszla' => true,
                'teljdat' => Carbon::now()->format('Y.m.d'),
                'fizhat' => Carbon::now()->format('Y.m.d'),
                'arfolyam' => 1, // TODO: Ezt honnan tudjam?
                'valuta' => 'HUF',
                'uzletag' => 'A',
                //'shop' => 'biobubi.hu',
                'email' => $order['order']->email,
                'telefon' => $order['order']->phone,
                'fiz_mod' => $this->getTharanisPaymentMethodByName($order['order']->paymentMethodName),
                'megjegyzes' => $order['order']->comment,
                'szla_partkod' => '',
                'szla_nev' => sprintf('%s %s', $order['order']->firstname, $order['order']->lastname),
                'szla_orszag' => $order['order']->paymentCountryName, // TODO: Ezt ISO3-ra kéne alakítanunk
                'szla_irsz' => $order['order']->paymentPostcode,
                'szla_telepul' => $order['order']->paymentCity,
                'szla_utca' => trim(sprintf('%s %s', $order['order']->paymentAddress1, $order['order']->paymentAddress2)),
                'szall_mod' => $this->getTharanisShippingMethodByName($order['order']->shippingMethodLocalizedName),
                'szall_nev' => sprintf('%s %s', $order['order']->shippingFirstname, $order['order']->shippingLastname),
                'szall_orszag' => $order['order']->shippingCountryName, // TODO: ISO3 ide is
                'szall_irsz' => $order['order']->shippingPostcode,
                'szall_telepul' => $order['order']->shippingCity,
                'szall_utca' => trim(sprintf('%s %s', $order['order']->shippingAddress1, $order['order']->shippingAddress2)),
            ],
            'tetelek' => [
                'tetel' => []
            ]
        ];

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
                    'netto_ar' => strval($total->value/1.27),
                    'menny' => '1',
                ];
            } else {
                if ($total->type == 'SHIPPING' && intval($total->value) > 0) {
                    $data['tetelek']['tetel'][] = [
                        'raktar' => '1',
                        'cikksz' => 'glsszk',
                        'netto_ar' => strval($total->value/1.27),
                        'menny' => '1',
                    ];
                }
                if ($total->type == 'LOYALTYPOINTS_TO_USE' && intval($total->value) < 0) {
                    $data['tetelek']['tetel'][] = [
                        'raktar' => '1',
                        'cikksz' => 'Huseg2',
                        'netto_ar' => strval($total->value/1.27),
                        'menny' => '1',
                    ];
                }
                if ($total->type == 'PAYMENT') {
                    $data['tetelek']['tetel'][] = [
                        'raktar' => '1',
                        'cikksz' => 'utanvet',
                        'netto_ar' => strval($total->value/1.27),
                        'menny' => '1',
                    ];
                }
            }
        }

        $xml = ArrayToXml::convert($data, 'szamla', true, 'UTF-8');
        return $this->berak('kimeno_szamla', $xml);
    }

    public function getShippingMethod() {
        $products = $this->leker('cikk');
        if ($products['hiba'] == 1) {
            return $products;
        }

        $results = [];
        foreach ($products['valasz']['elem'] as $product) {
            //echo $product['alap']['megnev']['hu'] . "<br>";
            if (in_array($product['alap']['megnev']['hu'], ['Kupon kedvezmény', 'Hűségpont kedvezmény'])) {
                $results[] = $product;
            }
        }

        return $results;
    }

    public function test() {
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
        return $this->leker( 'cikk', $xml);
    }

    public function stock($skus) {
        $data = [
            'szurok' => [
                'szuro' => [
                    'mezo' => 'cikksz',
                    'relacio' => [
                        '_cdata' => '='
                    ],
                    'ertek' => implode('|', $skus),
                ],
            ],
        ];

        $xml = ArrayToXml::convert($data, 'leker', true, 'UTF-8');
        return $this->leker( 'keszlet', $xml);
    }

    public function getPaymentMethods() {
        return $this->leker( 'fiz_mod');
    }

    private function leker($function, $xml = null) {
        $data = $this->client->leker($this->customerCode, $this->companyCode, $this->apiKey, $function, $xml);
        $xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_NOCDATA);
        return json_decode(json_encode($xml),TRUE);
    }

    private function berak($function, $xml = null) {
        $data = $this->client->berak($this->customerCode, $this->companyCode, $this->apiKey, $function, $xml);
        $xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_NOCDATA);
        return json_decode(json_encode($xml), TRUE);
    }

    private function getTharanisPaymentMethodByName($paymentMethod): string {
        return match ($paymentMethod) {
            "Online bankkártyás fizetés" => "Bankkártya Web",
            "Gls Utánvét", "Utánvétel" => "Utánvét",
            default => $paymentMethod,
        };
    }

    private function getTharanisShippingMethodByName($shippingMethod): string {
        return match ($shippingMethod) {
            "Biobubi futár" => "Biobubi futár",
            "FoxPost Csomagautomata" => "FoxPost Csomagautomata",
            "Gls házhozszállítás" => "GLS",
            "GLS Csomagautomata" => "GLS Csomagpont",
            "Gls házhozszállítás flakonvisszaküldéssel" => "Gls házhozszállítás flakonvisszaküldéssel",
            default => $shippingMethod,
        };
    }
}