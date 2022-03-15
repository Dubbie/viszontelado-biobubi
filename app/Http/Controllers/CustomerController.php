<?php

namespace App\Http\Controllers;


use App\Customer;
use App\Order;
use App\Subesz\CustomerService;
use App\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;

class CustomerController extends Controller
{
    /** @var CustomerService */
    private $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index(Request $request)
    {
        $filter = [];

        if ($request->has('filter-reseller')) {
            $filter['reseller'] = $request->input('filter-reseller');
        }
        if ($request->has('filter-status')) {
            $filter['status'] = $request->input('filter-status');
        }
        if ($request->has('filter-query')) {
            $filter['query'] = $request->input('filter-query');
        }

        $customers = $this->customerService->getCustomersFiltered($filter);

        $resellers = [];
        foreach (User::all() as $u) {
            if ($u->id == Auth::id()) {
                continue;
            }

            $resellers[] = $u;
        }

        return view('customer.index')->with([
            'filter' => $filter,
            'customers' => $customers,
            'resellers' => $resellers,
        ]);
    }

    public function show($customerId)
    {
        $customer = null;
        if (Auth::user()->admin) {
            $customer = Customer::find($customerId);
        } else {
            Auth::user()->customers()->find($customerId);
        }

        if (!$customer) {
            Log::info('Nincs ilyen ügyfele a viszonteladónak!');
            return view('customer.index')->with([
                'error' => 'Nincs ilyen azonosítójú ügyféle.'
            ]);
        }

        return view('customer.show')->with([
            'customer' => $customer
        ]);
    }

    /**
     * Újragenerálja az összes ügyfelet a jelenlegi adatok alapján.
     */
    public function regenerateCustomers()
    {
        $orders = Order::all();
        DB::table('customers')->truncate();

        Log::info('Ügyfelek újragenerálása...');
        $startTime = microtime(true);
        foreach ($orders as $order) {
            // Megkeressük, hogy létezik-e az ügyfél
            $customer = Customer::where('email', $order->email)->first();
            if (!$customer) {
                $customer = new Customer();
                $customer->firstname = $order->firstname;
                $customer->lastname = $order->lastname;
                $customer->email = $order->email;
                $customer->phone = $order->phone;
                $customer->postcode = $order->shipping_postcode;
                $customer->city = $order->shipping_city;
                $customer->address = $order->shipping_address;
                $customer->user_id = $order->reseller_id;
                $customer->save();
            }
        }
        $endTime = microtime(true);
        Log::info(sprintf('Ügyfelek újragenerálása befejeződött (%.2f másodperc alatt)', ($endTime - $startTime)));
    }
}
