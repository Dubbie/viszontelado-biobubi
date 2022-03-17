<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Subesz\CustomerService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;

class CustomerController extends Controller
{
    /** @var CustomerService */
    private $customerService;

    public function __construct(CustomerService $customerService) {
        $this->customerService = $customerService;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request) {
        if (Auth::user()->admin && Customer::count() == 0) {
            try {
                $this->customerService->createInitialCustomers();
            } catch (\Exception $e) {
                Log::error('Hiba az ügyfelek generálásakor');
            }
        }

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
            'filter'    => $filter,
            'customers' => $customers,
            'resellers' => $resellers,
        ]);
    }

    public function show($customerId) {
        if (Auth::user()->admin) {
            $customer = Customer::find($customerId);
        } else {
            $customer = Auth::user()->customers()->find($customerId);
        }

        if (! $customer) {
            Log::info('Nincs ilyen ügyfele a viszonteladónak!');

            return redirect(action([CustomerController::class, 'index']))->with([
                'error' => 'Nincs ilyen azonosítójú ügyféle.',
            ]);
        }

        return view('customer.show')->with([
            'customer' => $customer,
        ]);
    }
}
