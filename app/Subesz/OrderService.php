<?php

namespace App\Subesz;


use App\Order;
use App\User;
use App\UserZip;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /** @var ShoprenterService */
    private $shoprenterApi;

    /** @var array */
    private $statusMap;

    /**
     * OrderService constructor.
     * @param ShoprenterService $shoprenterService
     */
    public function __construct(ShoprenterService $shoprenterService)
    {
        $this->shoprenterApi = $shoprenterService;

        $osds = $this->shoprenterApi->getAllStatuses();

        foreach ($osds->items as $osd) {
            $orderStatusId = str_replace(sprintf('%s/orderStatuses/', env('SHOPRENTER_API')), '', $osd->orderStatus->href);

            $this->statusMap[$orderStatusId] = [
                'name' => $osd->name,
                'color' => $osd->color,
            ];
        }
    }

    /**
     * @param $filter
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getOrdersFiltered($filter = [])
    {
        // Viszonteladó filter
        $userId = Auth::id();
        if (array_key_exists('reseller', $filter)) {
            $userId = intval($filter['reseller']);
        }

        $orders = $this->getOrdersQueryByUserId($userId);

        // Filter
        if (array_key_exists('query', $filter)) {
            $searchValue = '%' . $filter['query'] . '%';
            $orders = $orders->where(function ($query) use ($searchValue) {
                $query->where('firstname', 'like', $searchValue)
                    ->orWhere('lastname', 'like', $searchValue)
                    ->orWhere('shipping_address', 'like', $searchValue)
                    ->orWhere('email', 'like', $searchValue);
            });
        }

        // Státusz
        if (array_key_exists('status', $filter)) {
            $orders = $orders->where('status_text', '=', $filter['status']);
        }

        return $orders->orderBy('created_at', 'desc')->get();
    }

    /**
     * @param $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getOrdersQueryByUserId($userId) {
        $user = User::find($userId);

        $userZips = array_column($user->zips->toArray(), 'zip');
        $resellerZips = array_column(UserZip::select('zip')->whereNotIn('zip', $userZips)->get()->toArray(), 'zip');

        $orders = Order::query();

        if ($user->admin && count($user->zips) > 0) {
            // Kiszedjük azokat amik megfeleltek a feltételeknek
            $orders = $orders->where(function ($query) use ($userZips, $resellerZips) {
                $query->whereIn('shipping_postcode', $userZips)->orWhereNotIn('shipping_postcode', $resellerZips);
            });
        } else if (!$user->admin) {
            $orders = $orders->where(function ($query) use ($userZips) {
                $query->whereIn('shipping_postcode', $userZips)->orderBy('created_at', 'desc');
            });
        }

        return $orders;
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function getOrdersByUserId($userId)
    {
        $user = User::find($userId);

        // Ha admin és van irányítószáma akkor nézzük meg, hogy mik azok a megrendelések amikhez nincs viszonteladói irányítószám
        $userZips = array_column($user->zips->toArray(), 'zip');
        $resellerZips = array_column(UserZip::select('zip')->whereNotIn('zip', $userZips)->get()->toArray(), 'zip');

        if ($user->admin && count($user->zips) == 0) {
            return Order::orderBy('created_at', 'desc')->get();
        } else if ($user->admin && count($user->zips) > 0) {
            // Kiszedjük azokat amik megfeleltek a feltételeknek
            return Order::whereIn('shipping_postcode', $userZips)->orWhereNotIn('shipping_postcode', $resellerZips)->orderBy('created_at', 'desc')->get();
        } else {
            // Kiszedjük azokat amik megfeleltek a feltételeknek
            return Order::whereIn('shipping_postcode', $userZips)->orderBy('created_at', 'desc')->get();
        }
    }

    /**
     * @param \stdClass $order
     * @param bool $muted
     * @return bool
     */
    public function updateLocalOrder($order, $muted = false) {
        if (!$muted) {
            Log::info('-- Megrendelés frissítése --');
        }
        $local = Order::where('inner_resource_id', $order->id)->first();
        if (!$local) {
            if (!$muted) {
                Log::info(sprintf("A keresett megrendelés nem létezik (Azonosító: '%s')", $order->id));
                Log::info('Új megrendelés létrehozása...');
            }
            $local = new Order();
        }

        $tax = ($order->paymentMethodTaxRate + 100) / 100;
        $total = $order->total / $tax;
        $taxPrice = intval($order->total) - $total;
        $totalGross = intval($order->total);
        $orderStatusId = str_replace(sprintf('%s/orderStatuses/', env('SHOPRENTER_API')), '', $order->orderStatus->href);

        $local->inner_id = $order->innerId;
        $local->inner_resource_id = $order->id;
        $local->total = $total;
        $local->total_gross = $totalGross;
        $local->tax_price = $taxPrice;
        $local->firstname = $order->firstname;
        $local->lastname = $order->lastname;
        $local->email = $order->email;
        $local->status_text = $this->statusMap[$orderStatusId]['name'];
        $local->status_color = $this->statusMap[$orderStatusId]['color'];
        $local->shipping_method_name = $order->shippingMethodName;
        $local->payment_method_name = $order->paymentMethodName;
        $local->shipping_postcode = $order->shippingPostcode;
        $local->shipping_city = $order->shippingCity;
        $local->shipping_address = sprintf('%s %s', $order->shippingAddress1, $order->shippingAddress2);
        $local->created_at = date('Y-m-d H:i:s', strtotime($order->dateCreated));
        $local->updated_at = date('Y-m-d H:i:s');


        if ($local->save()) {
            if(!$muted) {
                Log::info(sprintf('Megrendelés mentve (Azonosító : %s)', $local->id));
            }
            return $local;
        } else {
            return false;
        }
    }

    /**
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null|object
     */
    public function getLatestOrder($limit = 1) {
        $order = $this->getOrdersQueryByUserId(Auth::id());
        return $order->orderBy('created_at', 'DESC')->limit($limit)->get();
    }

    /**
     * @return mixed
     */
    public function getLastUpdate() {
        $lastOrder = Order::orderBy('updated_at')->first();

        if (!$lastOrder) {
            return null;
        }

        return $lastOrder->updated_at;
    }

    /**
     * @return string
     */
    public function getLastUpdateHuman() {
        /** @var Carbon $last */
        $last = $this->getLastUpdate();

        if ($last) {
            return $last->diffForHumans();
        }

        return '';
    }

    /**
     * @param $resourceId
     * @return mixed
     */
    public function getLocalOrderByResourceId($resourceId) {
        return Order::where('inner_resource_id', $resourceId)->first();
    }

    /**
     * @param $order
     * @return string
     */
    public function getFormattedAddress($order)
    {
        $out = '';

        if ($order->shippingPostcode && $order->shippingCity && $order->shippingAddress1) {
            $out = sprintf('%s %s, %s', $order->shippingPostcode, $order->shippingCity, $order->shippingAddress1);
        }

        return $out;
    }
}