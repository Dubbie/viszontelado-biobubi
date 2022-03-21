<?php

namespace App\Subesz;

use App\Customer;
use App\CustomerCall;
use App\Order;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Log;

class CustomerService
{
    private $startDate;

    public function __construct() {
        $this->startDate = Carbon::parse('2022-03-07');
    }

    /**
     * @param  array  $filter
     * @return LengthAwarePaginator
     */
    public function getCustomersFiltered(array $filter = []): LengthAwarePaginator {
        // Viszonteladó filter
        $customers = Customer::where('user_id', '=', Auth::id());

        if (Auth::user()->admin && array_key_exists('reseller', $filter)) {
            if ($filter['reseller'] == 'ALL') {
                $customers = Customer::where('user_id', '!=', null);
            } else {
                $customers = Customer::where('user_id', '=', intval($filter['reseller']));
            }
        }

        // Query Filter
        if (array_key_exists('query', $filter)) {
            $searchValue = '%'.$filter['query'].'%';
            $customers   = $customers->where(function ($query) use ($searchValue) {
                $query->where('firstname', 'like', $searchValue)->orWhere('lastname', 'like', $searchValue)->orWhere('address', 'like', $searchValue)->orWhere('email', 'like', $searchValue);
            });
        }

        return $customers->orderBy('firstname')->paginate(50)->onEachSide(1);
    }

    /**
     * @param  array  $filter
     * @return LengthAwarePaginator
     */
    public function getCustomerCallsFiltered(array $filter = []): LengthAwarePaginator {
        // Viszonteladó filter
        $cc = CustomerCall::where('user_id', '=', Auth::id());

        if (Auth::user()->admin && array_key_exists('reseller', $filter)) {
            if ($filter['reseller'] == 'ALL') {
                $cc = CustomerCall::where('user_id', '!=', null);
            } else {
                $cc = CustomerCall::where('user_id', '=', intval($filter['reseller']));
            }
        }

        return $cc->orderBy('called_at')->orderBy('due_date')->paginate(50)->onEachSide(1);
    }

    /**
     * Létrehozza az első timereket
     */
    public function createInitialTimers() {
        set_time_limit(0);
        Log::info('Hívandóak lista újragenerálása...');
        CustomerCall::truncate();
        Log::info('- Hívandóak lista törölve');

        $customers = Customer::withCount('orders')->having('orders_count', '=', 1)->get();
        $count     = 0;
        foreach ($customers as $customer) {
            $order = $customer->orders()->first();
            if ($order->created_at > $this->startDate) {
                $cc              = new CustomerCall();
                $cc->user_id     = $customer->user_id;
                $cc->customer_id = $customer->id;
                $cc->due_date    = $order->created_at->addDays(14);
                $cc->save();
                $count++;
            }
        }

        Log::info(sprintf('- %d hívandó ügyfél generálva', $count));
    }

    /**
     * @throws \Exception
     */
    public function createInitialCustomers() {
        $orders = Order::all();
        set_time_limit(0);
        Customer::query()->delete();

        Log::info('Ügyfelek újragenerálása...');
        $startTime = microtime(true);
        foreach ($orders as $order) {
            if ($order->status_text != 'Törölve') {
                $this->createCustomerFromLocalOrder($order);
            }
        }
        $endTime = microtime(true);
        Log::info(sprintf('Ügyfelek újragenerálása befejeződött (%.2f másodperc alatt)', ($endTime - $startTime)));
    }

    /**
     * @param  \App\Order  $localOrder
     * @return Customer
     */
    public function createCustomerFromLocalOrder(Order $localOrder): Customer {
        // Megkeressük, hogy létezik-e az ügyfél
        $customer = Customer::where('email', $localOrder->email)->first();
        if (! $customer) {
            $customer            = new Customer();
            $customer->firstname = $localOrder->firstname;
            $customer->lastname  = $localOrder->lastname;
            $customer->email     = $localOrder->email;
            $customer->phone     = $localOrder->phone;
            $customer->postcode  = $localOrder->shipping_postcode;
            $customer->city      = $localOrder->shipping_city;
            $customer->address   = $localOrder->shipping_address;
            $customer->user_id   = $localOrder->reseller_id;
            $customer->save();
        }

        return $customer;
    }

    /**
     * Létrehozza a hívandó objektumot 14 nappal később, mint a megadott kezdő dátum
     *
     * @param          $customerId
     * @param  Carbon  $startDate
     */
    public function createCall($customerId, Carbon $startDate) {
        $customer        = Customer::find($customerId);
        $cc              = new CustomerCall();
        $cc->user_id     = $customer->user_id;
        $cc->customer_id = $customer->id;
        $cc->due_date    = $startDate->addDays(14);
        $cc->save();
    }

    /**
     * @param $customerId
     * @return bool
     */
    public function removeCall($customerId): bool {
        $cc = CustomerCall::where('customer_id', $customerId)->first();

        if ($cc) {
            try {
                $cc->delete();

                return true;
            } catch (Exception $e) {
                Log::error('Hiba történt a hívandó törlésekor (szervízből)');

                return false;
            }
        }

        return true;
    }
}
