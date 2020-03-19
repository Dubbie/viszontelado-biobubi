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
    /** @var Request */
    private $billingo;

    /**
     * BillingoService constructor.
     */
    public function __construct()
    {
        $this->billingo = new Request([
            'public_key' => env('BILLINGO_PUBLIC_KEY'),
            'private_key' => env('BILLINGO_PRIVATE_KEY'),
        ]);
    }

    /**
     * @param $order
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface
     */
    public function createInvoiceFromOrder($order)
    {
        $createdAt = Carbon::now();
        $due = $createdAt->copy()->addDays(8);

        $clientData = [
            'name' => sprintf('%s %s', $order['shippingFirstname'], $order['shippingLastname']),
            'email' => $order['email'],
            'billing_address' => [
                'country' => $order['shippingCountryName'],
                'postcode' => $order['shippingPostcode'],
                'city' => $order['shippingCity'],
                'street_name' => trim(sprintf('%s %s', $order['shippingAddress1'], $order['shippingAddress2'])),
            ],
            'phone' => $order['phone'],
        ];

        $client = null;
        try {
            $client = $this->billingo->post('clients', $clientData);

            $items = [];
            foreach ($order['orderProducts']['orderProduct'] as $item) {
                $vatId = $item['taxRate'] == "27.0000" ? 1 : null;
                if (!$vatId) {
                    return "Lekezeletlen áfa azonosító! (" . $item['taxRate'] . ")";
                }

                $items[] = [
                    'description' => $item['name'],
                    'qty' => intval($item['quantity']),
                    'unit' => 'db',
                    'vat_id' => $vatId,
                    'net_unit_price' => intval($item['price']),
                ];
            }

//            foreach ($order['totals'] as $total) {
//                if ($total->type == 'COUPON') {
//                    $items[] = [
//                        'description' => 'Kupon kedvezmény',
//                        'qty' => 1,
//                        'unit' => 'db',
//                        'vat_id' => 1,
//                        'gross_unit_price' => floatval($total->value),
//                    ];
//                }
//            }

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

            $invoice = $this->billingo->post('invoices', $invoiceData);
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
}