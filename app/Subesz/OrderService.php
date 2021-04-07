<?php

namespace App\Subesz;

use App\Order;
use App\OrderProducts;
use App\RegionZip;
use App\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use stdClass;

class OrderService
{
    /** @var ShoprenterService */
    private $shoprenterApi;

    /** @var array */
    private $statusMap;

    /** @var array */
    private $completedStatusMap;

    /**
     * OrderService constructor.
     *
     * @param  ShoprenterService  $shoprenterService
     */
    public function __construct(ShoprenterService $shoprenterService) {
        $this->shoprenterApi = $shoprenterService;

        $osds = $this->shoprenterApi->getAllStatuses();

        if (! $osds || ! property_exists($osds, 'items')) {
            return redirect(action('UserController@home'))->with([
                'error' => 'Hiba történt a Shoprenter API-hoz való kapcsolódáskor. Próbáld újra később.',
            ]);
        }

        // Teljesített státusz ID-k
        $this->completedStatusMap = [
            'b3JkZXJTdGF0dXMtb3JkZXJfc3RhdHVzX2lkPTU=', // Teljesítve
            'b3JkZXJTdGF0dXMtb3JkZXJfc3RhdHVzX2lkPTI0', // FOXPOST Teljesítve
        ];

        // Feltöljük a státusz mapot
        foreach ($osds->items as $osd) {
            $orderStatusId = str_replace(sprintf('%s/orderStatuses/', env('SHOPRENTER_API')), '', $osd->orderStatus->href);

            $this->statusMap[$orderStatusId] = [
                'name'  => $osd->name,
                'color' => $osd->color,
            ];
        }
    }

    /**
     * @param $filter
     * @return LengthAwarePaginator|Builder[]|Collection
     */
    public function getOrdersFiltered($filter = []) {
        // Viszonteladó filter
        $orders = Order::where('reseller_id', '=', Auth::id());

        if (Auth::user()->admin && array_key_exists('reseller', $filter)) {
            if ($filter['reseller'] == 'ALL') {
                $orders = Order::where('reseller_id', '!=', null);
            } else {
                $orders = Order::where('reseller_id', '=', intval($filter['reseller']));
            }
        }

        // Filter
        if (array_key_exists('query', $filter)) {
            $searchValue = '%'.$filter['query'].'%';
            $orders      = $orders->where(function ($query) use ($searchValue) {
                $query->where('firstname', 'like', $searchValue)->orWhere('lastname', 'like', $searchValue)->orWhere('shipping_address', 'like', $searchValue)->orWhere('inner_id', 'like', $searchValue)->orWhere('email', 'like', $searchValue);
            });
        }

        // Filter
        if (array_key_exists('with_products', $filter)) {
            $orders->has('products');
        }

        // Régió
        if (array_key_exists('region', $filter)) {
            /** @var \App\Region $region */
            $region = Auth::user()->regions()->find($filter['region']);
            if ($region) {
                $orders = $orders->whereIn('shipping_postcode', $region->zips->pluck('zip')->toArray());
            }
        }

        // Státusz
        if (array_key_exists('status', $filter)) {
            $orders = $orders->where('status_text', '=', $filter['status']);
        }

        return $orders->orderBy('created_at', 'desc')->paginate(50)->onEachSide(1);
    }

    /**
     * @param  stdClass  $order
     * @param  bool      $muted
     * @return \App\Order|bool|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object
     */
    public function updateLocalOrder($order, $muted = false) {
        if (! $muted) {
            Log::info('-- Megrendelés frissítése --');
        }
        $local = Order::where('inner_resource_id', $order->id)->first();
        if (! $local) {
            if (! $muted) {
                Log::info(sprintf("A keresett megrendelés nem létezik (Azonosító: '%s')", $order->id));
                Log::info('Új megrendelés létrehozása...');
            }
            $local = new Order();
        }

        $tax        = ($order->paymentMethodTaxRate + 100) / 100;
        $total      = round($order->total / $tax);
        $taxPrice   = round($order->total - $total);
        $totalGross = round($order->total);

        $orderStatusId = str_replace(sprintf('%s/orderStatuses/', env('SHOPRENTER_API')), '', $order->orderStatus->href);

        if (! array_key_exists($orderStatusId, $this->statusMap)) {
            Log::error('Nem volt megtalálható a státusz azonosító a státusz leíró térképben.');
            Log::error(var_dump($this->statusMap));

            return false;
        }

        $local->fill([
            'inner_id'             => $order->innerId,
            'inner_resource_id'    => $order->id,
            'total'                => $total,
            'total_gross'          => $totalGross,
            'tax_price'            => $taxPrice,
            'firstname'            => $order->firstname,
            'lastname'             => $order->lastname,
            'email'                => $order->email,
            'phone'                => $order->phone,
            'status_text'          => $this->statusMap[$orderStatusId]['name'],
            'status_color'         => $this->statusMap[$orderStatusId]['color'],
            'shipping_method_name' => $order->shippingMethodName,
            'payment_method_name'  => $order->paymentMethodName,
            'shipping_postcode'    => $order->shippingPostcode,
            'shipping_city'        => $order->shippingCity,
            'shipping_address'     => sprintf('%s %s', $order->shippingAddress1, $order->shippingAddress2),
            'created_at'           => date('Y-m-d H:i:s', strtotime($order->dateCreated)),
            'updated_at'           => date('Y-m-d H:i:s'),
        ]);

        if ($local->save()) {
            if (! $muted) {
                Log::info(sprintf('Megrendelés mentve (Azonosító : %s)', $local->id));
            }

            return $local;
        } else {
            return false;
        }
    }

    /**
     * @param  int  $limit
     * @return Builder|Model|null|object
     */
    public function getLatestOrder($limit = 1) {
        $order = $this->getOrdersQueryByUserId(Auth::id());

        return $order->orderBy('created_at', 'DESC')->limit($limit)->get();
    }

    /**
     * @param $userId
     * @return Builder
     */
    public function getOrdersQueryByUserId($userId): Builder {
        return Order::where('reseller_id', $userId)->orderBy('created_at', 'desc');
    }

    /**
     * @return string
     */
    public function getLastUpdateHuman(): string {
        /** @var Carbon $last */
        $last = $this->getLastUpdate();

        if ($last) {
            return $last->diffForHumans();
        }

        return '';
    }

    /**
     * @return mixed
     */
    public function getLastUpdate() {
        /** @var Order $lastOrder */
        $lastOrder = Order::orderBy('updated_at')->first();
        if (! $lastOrder) {
            return null;
        }

        return $lastOrder->updated_at;
    }

    /**
     * @param $resourceId
     * @return mixed
     */
    public function getLocalOrderByResourceId($resourceId) {
        return Order::where('inner_resource_id', $resourceId)->first();
    }

    /**
     * Visszaadja a megrendelésből a megrendelt termékeket és darabszámukat.
     *
     * @param  array  $order
     * @return array
     */
    public function getOrderedProductsFromOrder(array $order): array {
        $productsList = [];
        foreach ($order['products']->items as $item) {
            $productsList[] = [
                'sku'   => $item->sku,
                'count' => intval($item->stock1),
            ];
        }

        return $productsList;
    }

    /**
     * @param $order
     * @return string
     */
    public function getFormattedAddress($order): string {
        $out = '';

        if ($order->shippingPostcode && $order->shippingCity && $order->shippingAddress1) {
            $out = sprintf('%s %s, %s %s', $order->shippingPostcode, $order->shippingCity, $order->shippingAddress1, $order->shippingAddress2);
        }

        return $out;
    }

    /**
     * @param  string  $string
     * @return User|Builder|Model|mixed|null|object
     */
    public function getResellerByZip(string $string) {
        /** @var \App\RegionZip $rZip */
        $rZip = RegionZip::where('zip', $string)->first();
        if ($rZip) {
            return $rZip->reseller;
        } else {
            return User::where('email', 'hello@semmiszemet.hu')->first();
        }
    }

    /**
     * @param  array  $skuList
     * @param         $orderId
     */
    public function saveOrderedProducts(array $skuList, $orderId) {
        $ss = resolve('App\Subesz\StockService');

        foreach ($skuList as $orderedProduct) {
            \Log::info('-- -- Megrendelt termékek rögzítése az adatbázisba...');
            $lp = $ss->getLocalProductBySku($orderedProduct['sku']);

            foreach ($lp->getSubProducts() as $subProduct) {
                $op              = new OrderProducts();
                $op->order_id    = $orderId;
                $op->product_sku = $subProduct['product']->sku;
                $op->product_qty = $subProduct['count'] * $orderedProduct['count'];
                $op->save();
            }
            \Log::info('-- -- ... a megrendelt termékek rögzítése sikeres!');
        }
    }

    /**
     * @param  string  $orderResourceId
     * @param  string  $statusId
     * @return array
     */
    public function updateStatus(string $orderResourceId, string $statusId): array {
        $response = [
            'success' => true,
            'message' => 'Státusz frissítve',
        ];

        // Ha hibára fut a Shoprenter frissítés akkor visszatérünk
        if (! $this->shoprenterApi->updateOrderStatusId($orderResourceId, $statusId)) {
            $response['success'] = false;
            $response['message'] = 'Hiba történt a státusz frissítésekor a ShopRenterben.';

            return $response;
        }

        return $response;
    }

	/**
	 * @param int $orderID
	 * @return \Illuminate\Database\Query\Builder
	 * returns and order object based on the ID given
	 */
	public function getCommentsHTML(string $orderID) {
		try {
			$response['success'] = true;
			$response['order'] = Order::find($orderID);
		} catch (Exception $e) {
			$response['success'] = false;
			$response['message'] = "Nem található a kért megrendelés.";
		}
		return $response;
	}
}
