<?php

namespace App\Subesz;


class KlaviyoService
{
    /** @var string */
    private $publicApiKey;

    /**
     * KlaviyoService constructor.
     */
    public function __construct()
    {
        $this->publicApiKey = env('KLAVIYO_PUBLIC_API_KEY');
    }

    /**
     * @param $order
     * @return bool
     */
    public function trackOrder($order)
    {
        $orderedProducts = [];
        $placedOrder = [
            "token" => $this->publicApiKey,
            "event" => "Placed Order",
            "customer_properties" => [
                '$email' => $order['order']->email,
                '$first_name' => $order['order']->firstname,
                '$last_name' => $order['order']->lastname,
                '$phone_number' => $order['order']->phone,
                '$address1' => $order['order']->shippingAddress1,
                '$address2' => $order['order']->shippingAddress2,
                '$city' => $order['order']->shippingCity,
                '$zip' => $order['order']->shippingPostcode,
                '$country' => $order['order']->shippingCountryName,
            ],
            "properties" => [
                '$event_id' => $order['order']->id,
                '$value' => floatval($order['order']->total), // A teljes összeg
                "ItemNames" => [],
                "Items" => [],
                "BillingAddress" => [
                    "FirstName" => $order['order']->paymentFirstname,
                    "LastName" => $order['order']->paymentLastname,
                    "Company" => $order['order']->paymentCompany,
                    "Address1" => $order['order']->paymentAddress1,
                    "Address2" => $order['order']->paymentAddress2,
                    "City" => $order['order']->paymentCity,
                    "Country" => $order['order']->paymentCountryName,
                    "Zip" => $order['order']->paymentPostcode,
                    "Phone" => $order['order']->phone
                ],
                "ShippingAddress" => [
                    "FirstName" => $order['order']->shippingFirstname,
                    "LastName" => $order['order']->shippingLastname,
                    "Company" => $order['order']->shippingCompany,
                    "Address1" => $order['order']->shippingAddress1,
                    "Address2" => $order['order']->shippingAddress2,
                    "City" => $order['order']->shippingCity,
                    "Country" => $order['order']->shippingCountryName,
                    "Zip" => $order['order']->shippingPostcode,
                    "Phone" => $order['order']->phone
                ]
            ],
            "time" => strtotime($order['order']->dateCreated),
        ];

        $ss = resolve('App\Subesz\ShoprenterService');
        foreach ($order['products']->items as $product) {
            $srProduct = $ss->getProduct($product->sku);

            // Megrendeléshez tartozó termékek
            $placedOrder['properties']['Items'][] = [
                "ProductID" => $srProduct->innerId,
                "SKU" => $product->sku,
                "ProductName" => $product->name,
                "Quantity" => $product->stock1,
                "ItemPrice" => round($product->price * 1.27),
                "RowTotal" => round($product->total * 1.27),
                "ProductURL" => 'https://biobubi.hu/' . $srProduct->urlAliases[0]->urlAlias ?? 'https://biobubi.hu/',
                "ImageURL" => $srProduct->allImages->mainImage,
            ];
            $placedOrder['properties']['ItemNames'][] = $product->name;

            // Külön a megrendelt termékek
            $orderedProducts[] = [
                "token" => env('KLAVIYO_PUBLIC_API_KEY'),
                "event" => "Ordered Product",
                "customer_properties" => [
                    '$email'  => $order['order']->email,
                    '$first_name' => $order['order']->firstname,
                    '$last_name'  => $order['order']->lastname
                ],
                "properties" => [
                    '$event_id' => $order['order']->id . '_' . $product->sku,
                    '$value'  => round($product->price * 1.27),
                    "ProductID" => $srProduct->innerId,
                    "SKU" => $product->sku,
                    "ProductName" => $product->name,
                    "Quantity" => $product->stock1,
                    "ProductURL" => 'https://biobubi.hu/' . $srProduct->urlAliases[0]->urlAlias ?? 'https://biobubi.hu/',
                    "ImageURL" => $srProduct->allImages->mainImage,
                ],
                "time" => strtotime($order['order']->dateCreated),
            ];
        }

        // Összegző iteráció
        foreach ($order['totals'] as $total) {
            if ($total->type == 'COUPON') {
                $placedOrder['properties']['DiscountValue'] = abs($total->value);
                break;
            }
        }

        // Most pedig beküldjük
        $placedOrderSuccess = file_get_contents('https://a.klaviyo.com/api/track?data=' . urlencode(base64_encode(json_encode($placedOrder, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))));
        $orderedProductsSuccesses = [];
        foreach ($orderedProducts as $orderedProduct) {
            $orderedProductsSuccesses[] = file_get_contents('https://a.klaviyo.com/api/track?data=' . urlencode(base64_encode(json_encode($orderedProduct, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))));
        }

        return $placedOrderSuccess == '1';
    }
}