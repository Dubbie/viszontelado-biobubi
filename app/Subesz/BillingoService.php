<?php

namespace App\Subesz;


use App\Mail\RegularOrderCompleted;
use App\Mail\TrialOrderCompleted;
use App\Order;
use Billingo\API\Connector\Exceptions\JSONParseException;
use Billingo\API\Connector\Exceptions\RequestErrorException;
use Billingo\API\Connector\HTTP\Request;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class BillingoService
{
    /**
     * BillingoService constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param $order
     * @param $user
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface
     */
    public function createInvoiceFromOrder($order, $user)
    {
        if (!$this->userHasBillingoData($user)) {
            return false;
        }

        $billingo = new Request([
            'public_key' => $user->billingo_public_key,
            'private_key' => $user->billingo_private_key,
        ]);

        $createdAt = Carbon::parse($order['order']->dateCreated);
        $due = $createdAt->copy()->addDays(8);

        $clientData = [
            'name' => sprintf('%s %s', $order['order']->firstname, $order['order']->lastname),
            'email' => $order['order']->email,
            'billing_address' => [
                'country' => $order['order']->shippingCountryName,
                'postcode' => $order['order']->shippingPostcode,
                'city' => $order['order']->shippingCity,
                'street_name' => trim(sprintf('%s %s', $order['order']->shippingAddress1, $order['order']->shippingAddress2)),
            ],
            'phone' => $order['order']->phone,
        ];

        $client = null;
        try {
            $client = $billingo->post('clients', $clientData);

            $items = [];
            foreach ($order['products']->items as $item) {
                $netUnitPrice = $user->vat_id == 992 ? round($item->price * ((100 + floatval($item->taxRate)) / 100)) : floatval($item->price);

                $items[] = [
                    'description' => $item->name,
                    'qty' => intval($item->stock1),
                    'unit' => 'db',
                    'vat_id' => $user->vat_id,
                    'net_unit_price' => floatval($netUnitPrice),
                ];
            }

            foreach ($order['totals'] as $total) {
                if ($total->type == 'COUPON') {
                    $items[] = [
                        'description' => 'Kupon kedvezmény',
                        'qty' => 1,
                        'unit' => 'db',
                        'vat_id' => $user->vat_id,
                        'gross_unit_price' => round(floatval($total->value)),
                    ];
                } else if($total->type == 'SHIPPING' && intval($total->value) > 0) {
                    $items[] = [
                        'description' => 'Szállítási költség',
                        'qty' => 1,
                        'unit' => 'db',
                        'vat_id' => $user->vat_id,
                        'gross_unit_price' => round(floatval($total->value)),
                    ];
                }
            }

            $invoiceData = [
                'fulfillment_date' => $createdAt->format('Y-m-d'),
                'due_date' => $due->format('Y-m-d'),
                'payment_method' => 4,
                'comment' => '',
                'template_lang_code' => 'hu',
                'electronic_invoice' => 1,
                'currency' => 'HUF',
                'client_uid' => $client ? $client['id'] : null,
                'block_uid' => intval($user->block_uid),
                'type' => 3,
                'round_to' => 1,
                'items' => $items,
            ];

            $invoice = $billingo->post('invoices', $invoiceData);
            Log::info(dump($invoice));
            if ($invoice) {
                // Megkeressük a helyi változatát a megrendelésnek
                /** @var OrderService $os */
                $os = resolve('App\Subesz\OrderService');
                /** @var Order $local */
                $local = $os->getLocalOrderByResourceId($order['order']->id);
                $local->invoice_id = $invoice['id'];
                $local->save();

                Log::info(sprintf('Számla sikeresen létrejött! (Számla azonosító: %s)', $invoice['id']));
                return $invoice['id'];
            }
        } catch (JSONParseException $e) {
            Log::error(sprintf('Hiba történt az új kliens létrehozásakor! %s %s', $e->getMessage(), $e->getTraceAsString()));
            return false;
        } catch (RequestErrorException $e) {
            Log::error(sprintf('Hiba történt az új kliens létrehozásakor! %s %s', $e->getMessage(), $e->getTraceAsString()));
            return false;
        } catch (GuzzleException $e) {
            Log::error(sprintf('Hiba történt az új kliens létrehozásakor! %s %s', $e->getMessage(), $e->getTraceAsString()));
            return false;
        }

        return false;
    }

    /**
     * @param $user
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     */
    public function getVatList($user) {
        $billingo = $this->getBillingoRequest($user);

        try {
            return $billingo->get('vat');
        } catch (JSONParseException $e) {
            Log::error("Hiba a JSON átalakításakor");
        } catch (RequestErrorException $e) {
            Log::error("Hiba a lekérdezésben");
        } catch (GuzzleException $e) {
            Log::info('Hiba történt a Billingo API meghívásakor: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Elmenti a megadott számla azonosító alapján a számlát a megrendelés azonosítóhoz
     *
     * @param $user
     * @param $invoiceId
     * @param $orderId
     * @return bool|null|string
     */
    public function saveInvoice($user, $invoiceId, $orderId) {
        $billingo = $this->getBillingoRequest($user);

        try {
            $fname = 'ssz-szamla-' . date('Ymd_His') . '.pdf';
            $path = sprintf('invoices/%s/%s', $orderId, $fname);
            $data = $billingo->downloadInvoice($invoiceId);

            if (\Storage::put($path, $data->getContents())) {
                $localOrder = Order::find($orderId);
                $localOrder->invoice_path = $path;
                $localOrder->save();

                Log::info(sprintf('Számla sikeresen elmentve (Fájl: %s)', $path));
                return $path;
            } else {
                Log::info('Hiba történt a számla elmentésekor a rendszerbe!');
                return false;
            }
        } catch (GuzzleException $e) {
            Log::info('Hiba történt a Billingo API meghívásakor: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * @param $user
     * @param $invoiceId
     * @return null|\Psr\Http\Message\StreamInterface|string
     */
    public function downloadInvoice($user, $invoiceId) {
        $billingo = $this->getBillingoRequest($user);

        try {
            return $billingo->downloadInvoice($invoiceId, 'billingo-' . date('Ymd_His') . '.pdf');
        } catch (GuzzleException $e) {
            Log::info('Hiba történt a Billingo API meghívásakor: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * @param $user
     * @return bool
     */
    public function userHasBillingoData($user) {
        $userError = false;

        if ($user->billingo_public_key && strlen($user->billingo_public_key) == 0) {
            Log::error('Hiba a számla létrehozásakor!');
            Log::error(sprintf('A megadott felhasználóhoz nem lett megadva Billingo nyilvános kulcs! (Felhasználó: %s)', $user->name));
            $userError = true;
        }
        if ($user->billingo_private_key && strlen($user->billingo_private_key) == 0) {
            Log::error('Hiba a számla létrehozásakor!');
            Log::error(sprintf('A megadott felhasználóhoz nem lett megadva Billingo privát kulcs! (Felhasználó: %s)', $user->name));
            $userError = true;
        }
        if ($user->block_uid && strlen($user->block_uid) == 0) {
            Log::error('Hiba a számla létrehozásakor!');
            Log::error(sprintf('A megadott felhasználóhoz nem lett megadva Billingo számlázási tömb azonosító! (Felhasználó: %s)', $user->name));
            $userError = true;
        }

        return !$userError;
    }

    /**
     * @param $publicKey
     * @param $privateKey
     * @param $blockUid
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getBlockByUid($publicKey, $privateKey, $blockUid) {
        $billingo = new Request([
            'public_key' => $publicKey,
            'private_key' => $privateKey,
        ]);
        $response = [
            'success' => false,
            'messages' => [],
            'correctInputs' => [],
            'block' => null,
        ];

        try {
            $blocks = $billingo->get('invoices/blocks');

            if (count($blocks) < 1) {
                $response['messages'][] = 'Nem találhatóak számlatömbök a megadottak alapján';
                return $response;
            }

            $response['correctInputs'] = ['u-billingo-public-key', 'u-billingo-private-key'];
            $response['messages'][] = 'Az API-hoz történő csatlakozás sikeres volt.';

            $index = array_search($blockUid, array_column($blocks, 'id'));
            if ($index) {
                $response['correctInputs'][] = 'u-block-uid';
                $response['messages'][] = 'Helyes számlatömb azonosító.';
                $response['messages'][] = sprintf('(Számlatömb: %s)', $blocks[$index]['attributes']['name']);
                $response['block'] = $blocks[$index];
                $response['success'] = true;
            } else {
                $response['messages'][] = 'Nem található ilyen számlatömb azonosító a számlatömbök között.';
            }
        } catch (JSONParseException $e) {
            echo "Error parsing response";
        } catch (RequestErrorException $e) {
            echo "Error in request";
        } catch (GuzzleException $e) {
            Log::info('Hiba történt a Billingo API Teszt során: ' . $e->getMessage());
            $response['messages'][] = 'Nem sikerült csatlakozni az API-hoz';
        }

        return $response;
    }

    /**
     * @param $user
     * @return Request
     */
    private function getBillingoRequest($user) {
        return new Request([
            'public_key' => $user->billingo_public_key,
            'private_key' => $user->billingo_private_key,
        ]);
    }
}