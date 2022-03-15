<?php


namespace App\Subesz;


use App\Customer;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class CustomerService
{
    private $startDate;

    public function __construct()
    {
        $this->startDate = Carbon::parse('2022-03-07');
    }

    /**
     * @param  array  $filter
     * @return LengthAwarePaginator
     */
    public function getCustomersFiltered(array $filter = []): LengthAwarePaginator
    {
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
            $customers = $customers->where(function ($query) use ($searchValue) {
                $query->where('firstname', 'like', $searchValue)
                    ->orWhere('lastname', 'like', $searchValue)
                    ->orWhere('address', 'like', $searchValue)
                    ->orWhere('email', 'like', $searchValue);
            });
        }

        return $customers->orderBy('firstname')->paginate(50)->onEachSide(1);
    }

    public function createInitialTimers()
    {

    }
}
