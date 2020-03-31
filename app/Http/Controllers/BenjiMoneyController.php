<?php

namespace App\Http\Controllers;

use App\BenjiMoney;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BenjiMoneyController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function getData(Request $request) {
        $input = $request->validate([
            'start-date' => 'required',
            'end-date' => 'required',
        ]);

        $benji = User::where('email', 'Gbenji20@gmail.com')->first();

        if (!$benji) {
            return '<p class="text-danger-pastel mb-0">Benjinek még nincs fiókja :(</p>';
        }

        $deliveries = $benji->deliveries()->where([
            ['delivered_at', '>=', Carbon::parse($input['start-date'])],
            ['delivered_at', '<=', Carbon::parse($input['end-date'] . ' 23:59:59')]
        ])->get();

        // Odaadott pénzek kiszedése
        $benjiMoney = BenjiMoney::where([
            ['given_at', '>=', Carbon::parse($input['start-date'])],
            ['given_at', '<=', Carbon::parse($input['end-date'] . ' 23:59:59')]
        ])->get();

        return view('inc.user-deliveries')->with([
            'deliveries' => $deliveries,
            'benjiMoney' => $benjiMoney,
        ]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function store(Request $request) {
        $data = $request->validate([
            'benji-money-amount' => 'required',
        ]);

        $bm = new BenjiMoney();
        $bm->amount = $data['benji-money-amount'];

        return [
            'success' => $bm->save()
        ];
    }
}
