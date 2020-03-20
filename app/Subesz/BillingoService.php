<?php

namespace App\Subesz;


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
                $vatId = $item->taxRate == "27.0000" ? 1 : null;
                if (!$vatId) {
                    Log::error("Lekezeletlen áfa azonosító! (" . $item->taxRate . ")");
                    Log::info(dump($item));
                    return false;
                }

                $items[] = [
                    'description' => $item->name,
                    'qty' => intval($item->stock1),
                    'unit' => 'db',
                    'vat_id' => $vatId,
                    'net_unit_price' => floatval($item->price),
                ];
            }

            foreach ($order['totals'] as $total) {
                if ($total->type == 'COUPON') {
                    $items[] = [
                        'description' => 'Kupon kedvezmény',
                        'qty' => 1,
                        'unit' => 'db',
                        'vat_id' => 1,
                        'gross_unit_price' => floatval($total->value),
                    ];
                } else if($total->type == 'SHIPPING' && intval($total->value) > 0) {
                    $items[] = [
                        'description' => 'Szállítási költség',
                        'qty' => 1,
                        'unit' => 'db',
                        'vat_id' => 1,
                        'gross_unit_price' => floatval($total->value),
                    ];
                }
            }

            $invoiceData = [
                'fulfillment_date' => $createdAt->format('Y-m-d'),
                'due_date' => $due->format('Y-m-d'),
                'payment_method' => 4,
                'comment' => '',
                'template_lang_code' => 'hu',
                'electronic_invoice' => 0,
                'currency' => 'HUF',
                'client_uid' => $client['id'],
                'block_uid' => intval(env('BILLINGO_BLOCK')),
                'type' => 0,
                'round_to' => 1,
                'items' => $items,
            ];

            $invoice = $billingo->post('invoices', $invoiceData);
            if ($invoice) {
                Log::info(sprintf('Számla sikeresen létrejött! (Számla azonosító: %s)', $invoice['id']));
                return true;
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
}