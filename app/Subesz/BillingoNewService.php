<?php

namespace App\Subesz;


use App\Order;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Swagger\Client\Api\DocumentApi;
use Swagger\Client\Api\DocumentBlockApi;
use Swagger\Client\Api\PartnerApi;
use Swagger\Client\ApiException;
use Swagger\Client\Configuration;
use Swagger\Client\Model\Country;
use Swagger\Client\Model\Currency;
use Swagger\Client\Model\Document;
use Swagger\Client\Model\DocumentInsert;
use Swagger\Client\Model\DocumentLanguage;
use Swagger\Client\Model\DocumentType;
use Swagger\Client\Model\Partner;
use Swagger\Client\Model\PartnerTaxType;
use Swagger\Client\Model\PartnerUpsert;
use Swagger\Client\Model\PaymentMethod;
use Swagger\Client\Model\Round;
use Swagger\Client\Model\UnitPriceType;
use Swagger\Client\Model\Vat;
use Symfony\Component\Translation\LoggingTranslator;

class BillingoNewService
{
    public function __construct()
    {

    }

    /**
     * @param User $user
     * @return Configuration
     */
    public function getBillingoConfig(User $user)
    {
        $config = Configuration::getDefaultConfiguration()->setApiKey('X-API-KEY', $user->billingo_api_key);
        return $config;
    }

    /**
     * @param User $user
     * @return PartnerApi
     */
    public function getPartnerApi(User $user)
    {
        $apiInstance = new PartnerApi(new Client(), $this->getBillingoConfig($user));
        return $apiInstance;
    }

    /**
     * @param User $user
     * @return DocumentApi
     */
    public function getDocumentApi(User $user)
    {
        $apiInstance = new DocumentApi(new Client(), $this->getBillingoConfig($user));
        return $apiInstance;
    }

    /**
     * @param User $user
     * @return DocumentBlockApi
     */
    public function getDocumentBlockApi(User $user)
    {
        $apiInstance = new DocumentBlockApi(new Client(), $this->getBillingoConfig($user));
        return $apiInstance;
    }

    /**
     * @param $order
     * @param $user
     * @return bool|null|Partner
     */
    public function createPartner($order, $user)
    {
        if ($order['order']->paymentCountryName != 'Magyarország') {
            \Log::error('A megrendelés nem magyarországról jött, nincs támogatva!');
            return false;
        }

        $partnerApi = $this->getPartnerApi($user);
        $partner = null;

        // 1. Generálunk partner upsertet
        $partnerUpsert = $this->getPartnerUpsertFromOrder($order);
        // 2. Elmentjük a partnert
        try {
            $partner = $partnerApi->createPartner($partnerUpsert);
        } catch (ApiException $e) {
            \Log::error(sprintf('Hiba történt a PartnerApi->createPartner meghívásakor: %s %s', $e->getMessage(), PHP_EOL));
        }

        return $partner;
    }

    /**
     * @param $order
     * @return PartnerUpsert|null
     */
    public function getPartnerUpsertFromOrder($order)
    {
        $taxType = property_exists($order['order'], 'paymentTaxnumber') ? PartnerTaxType::HAS_TAX_NUMBER : PartnerTaxType::NO_TAX_NUMBER;

        $partnerUpsertData = [
            'name' => sprintf('%s %s', $order['order']->firstname, $order['order']->lastname),
            'address' => [
                'country_code' => Country::HU,
                'post_code' => $order['order']->paymentPostcode,
                'city' => $order['order']->paymentCity,
                'address' => trim(sprintf('%s %s', $order['order']->paymentAddress1, $order['order']->paymentAddress2)),
            ],
            'emails' => [$order['order']->email],
            'taxcode' => $order['order']->paymentTaxnumber ?? null,
            'account_number' => '',
            'phone' => $order['order']->phone,
            'tax_type' => $taxType
        ];

        return new PartnerUpsert($partnerUpsertData);
    }

    /**
     * @param $invoiceId
     * @param User $user
     * @return null|Document
     */
    public function getInvoice($invoiceId, User $user)
    {
        $documentApi = $this->getDocumentApi($user);
        $invoice = null;

        try {
            $invoice = $documentApi->getDocument(intval($invoiceId));
        } catch (ApiException $e) {
            \Log::error(sprintf('Exception when calling DocumentApi->getDocument: %s %s', $e->getMessage(), PHP_EOL));
        }

        return $invoice;
    }

    /**
     * @param array $order
     * @param Partner $partner
     * @param User $user
     * @return null|\Swagger\Client\Model\Document
     */
    public function createDraftInvoice(array $order, Partner $partner, User $user)
    {
        $documentApi = $this->getDocumentApi($user);

        $createdAt = Carbon::parse($order['order']->dateCreated);
        $due = $createdAt->copy()->addDays(8);
        $invoice = null;

        $items = $this->getOrderItems($order, $user);

        $documentInsertData = [
            'partner_id' => $partner->getId(),
            'block_id' => $user->block_uid,
            'type' => DocumentType::DRAFT,
            'fulfillment_date' => $createdAt->format('Y-m-d'),
            'due_date' => $due->format('Y-m-d'),
            'payment_method' => PaymentMethod::CASH_ON_DELIVERY,
            'language' => DocumentLanguage::HU,
            'currency' => Currency::HUF,
            'conversion_rate' => 1,
            'electronic' => false,
            'paid' => false,
            'items' => $items,
            'settings' => [
                'round' => Round::ONE,
            ]
        ];

        $documentInsert = new DocumentInsert($documentInsertData);
        try {
            $invoice = $documentApi->createDocument($documentInsert);
        } catch (ApiException $e) {
            \Log::error(sprintf('Exception when calling DocumentApi->createDocument: %s %s', $e->getMessage(), PHP_EOL));
        }

        return $invoice;
    }

    /**
     * @param array $order
     * @param $user
     * @return array
     */
    public function getOrderItems(array $order, $user)
    {
        $items = [];
        $vat = $user->vat_id == 992 ? Vat::AAM : Vat::_27;

        foreach ($order['products']->items as $item) {
            $netUnitPrice = $vat == Vat::AAM ? round($item->price * ((100 + floatval($item->taxRate)) / 100)) : floatval($item->price);

            $items[] = [
                'name' => $item->name,
                'quantity' => intval($item->stock1),
                'unit' => 'db',
                'vat' => $vat,
                'unit_price' => floatval($netUnitPrice),
                'unit_price_type' => UnitPriceType::NET
            ];
        }

        foreach ($order['totals'] as $total) {
            if ($total->type == 'COUPON') {
                $items[] = [
                    'name' => 'Kupon kedvezmény',
                    'quantity' => 1,
                    'unit' => 'db',
                    'vat' => $vat,
                    'unit_price' => round(floatval($total->value)),
                    'unit_price_type' => UnitPriceType::GROSS
                ];
            } else if ($total->type == 'SHIPPING' && intval($total->value) > 0) {
                $items[] = [
                    'name' => 'Szállítási költség',
                    'quantity' => 1,
                    'unit' => 'db',
                    'vat' => $vat,
                    'unit_price' => round(floatval($total->value)),
                    'unit_price_type' => UnitPriceType::GROSS
                ];
            }
        }

        return $items;
    }

    /**
     * Elmenti a piszkozat számla azonosítóját a megrendeléshez
     *
     * @param Document $invoice
     * @param array $order
     * @return bool
     */
    public function saveDraftInvoice(Document $invoice, array $order)
    {
        /** @var OrderService $os */
        $os = resolve('App\Subesz\OrderService');
        $localOrder = $os->getLocalOrderByResourceId($order['order']->id);
        $localOrder->draft_invoice_id = $invoice->getId();
        if (!$localOrder->save()) {
            \Log::error(sprintf('Hiba történt a piszkozat számla rögzítésekor! (Helyi megrendelés azonosító: %s)', $localOrder->id));
            return false;
        }

        return true;
    }

    /**
     * @param int $invoiceId
     * @param User $user
     * @return null|Document
     */
    public function getRealInvoiceFromDraft(int $invoiceId, User $user)
    {
        $api = $this->getDocumentApi($user);
        $realInvoice = null;

        try {
            $draft = $api->getDocument($invoiceId);

            $documentInsertData = [
                'partner_id' => $draft->getPartner()->getId(),
                'block_id' => $draft->getBlockId(),
                'type' => DocumentType::INVOICE,
                'fulfillment_date' => $draft->getFulfillmentDate(),
                'due_date' => $draft->getDueDate(),
                'payment_method' => $draft->getPaymentMethod(),
                'language' => $draft->getLanguage(),
                'currency' => $draft->getCurrency(),
                'conversion_rate' => $draft->getConversionRate(),
                'electronic' => true,
                'paid' => $draft->getPaidDate() ? true : false,
                'items' => $draft->getItems(),
                'settings' => $draft->getSettings()
            ];

            $documentInsert = new DocumentInsert($documentInsertData);
            $realInvoice = $api->createDocument($documentInsert);
        } catch (ApiException $e) {
            \Log::error(sprintf('Exception when converting invoice: %s %s', $e->getMessage(), PHP_EOL));
        }

        return $realInvoice;
    }

    /**
     * @param int $invoiceId
     * @param int $orderId
     * @param User $user
     * @return bool|string
     */
    public function saveInvoice(int $invoiceId, int $orderId, User $user)
    {
        $api = $this->getDocumentApi($user);

        try {
            $fname = 'ssz-szamla-' . date('Ymd_His') . '.pdf';
            $path = sprintf('invoices/%s/%s', $orderId, $fname);
            $data = $api->downloadDocument($invoiceId);

            if (\Storage::put($path, $data)) {
                \Log::info(sprintf('Számla sikeresen elmentve (Fájl: %s)', $path));
                return $path;
            } else {
                \Log::info('Hiba történt a számla elmentésekor a rendszerbe!');
                return false;
            }
        } catch (ApiException $e) {
            \Log::error(sprintf('Exception when calling DocumentApi->downloadDocument: %s %s', $e->getMessage(), PHP_EOL));
        }

        return false;
    }

    /**
     * @param $invoiceId
     * @param Order $order
     * @param User $user
     * @return bool|string
     */
    public function downloadInvoice($invoiceId, Order $order, User $user) {
        $api = $this->getDocumentApi($user);

        // Megpróbáljuk lementeni...
        usleep(1000000);
        try {
            $tries = 0;

            $result = $api->downloadDocument($invoiceId);

            while ($result == "{\"error\":{\"message\":\"Document PDF has not generated yet.\"}}" && $tries <= 10) {
                $tries++;
                \Log::error("Nem sikerült letölteni a dokumentumot, még nem jött létre... Újrapróbálkozás 5 másodperc múlva...");
                usleep(5000000);
                $result = $api->downloadDocument($invoiceId);
            }

            \Log::error(sprintf('Összes próbálkozások száma: %s', $tries));
            if ($tries == 10) {
                \Log::error('-------------- GIGABAJVAN NINCS SZÁMLA LETÖLTVE! -----------------');
                \Log::error('-- Számla azonosító: ' . $invoiceId);
                \Log::error('-- Megrendelés azonosító: ' . $order->id);
                return false;
            }

            // Jó volt, van számla PDF-ünk, elmentjük
            $fname = 'ssz-szamla-' . date('Ymd_His') . '.pdf';
            $path = sprintf('invoices/%s/%s', $order->id, $fname);

            if (\Storage::put($path, $result)) {
                \Log::info(sprintf('Számla sikeresen elmentve (Fájl: %s)', $path));
                return $path;
            } else {
                \Log::info('Hiba történt a számla elmentésekor a rendszerbe!');
                return false;
            }
        } catch (ApiException $e) {
            \Log::error('Hiba történt a számla létrehozásakor!');
        }

        return false;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isBillingoConnected(User $user)
    {
        $api = $this->getDocumentBlockApi($user);
        $found = false;

        if (!$user->billingo_api_key || !$user->block_uid) {
            \Log::info('A felhasználónak nincs beállítva billingo összekötés');
            return $found;
        }

        try {
            $list = $api->listDocumentBlock(0, 100);

            foreach ($list->getData() as $block) {
                if ($user->block_uid == $block->getId()) {
                    $found = true;
                    return $found;
                }
            }
        } catch (ApiException $e) {
            \Log::error(sprintf('Exception when calling DocumentBlockApi->listDocumentBlock: %s %s', $e->getMessage(), PHP_EOL));
        }

        return $found;
    }

    /**
     * @param $apiKey
     * @param $blockId
     * @return array
     */
    public function isBillingoWorking($apiKey, $blockId)
    {
        $config = Configuration::getDefaultConfiguration()->setApiKey('X-API-KEY', $apiKey);
        $api = new DocumentBlockApi(new Client(), $config);
        $response = [
            'success' => false,
        ];

        try {
            $list = $api->listDocumentBlock(0, 100);

            foreach ($list->getData() as $block) {
                if ($blockId == $block->getId()) {
                    $response['success'] = true;
                    break;
                }
            }
        } catch (ApiException $e) {
            \Log::error(sprintf('Exception when calling DocumentBlockApi->listDocumentBlock: %s %s', $e->getMessage(), PHP_EOL));
        }

        return $response;
    }
}