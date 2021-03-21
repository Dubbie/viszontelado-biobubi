<?php

namespace App\Http\Controllers;

use App\Region;
use App\RegionZip;
use App\Subesz\RegionService;
use App\User;
use Illuminate\Http\Request;
use Log;

class RegionController extends Controller
{
    /** @var \App\Subesz\RegionService */
    private $regionService;

    /**
     * RegionController constructor.
     *
     * @param  \App\Subesz\RegionService  $regionService
     */
    public function __construct(RegionService $regionService) {
        $this->regionService = $regionService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index() {
        return view('region.index')->with([
            'regions' => Region::withCount('zips')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create() {
        return view('region.create')->with([
            'resellers' => User::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function show(Region $region) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  $region
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function edit($region) {

        return view('region.edit')->with([
            'region'    => Region::find($region),
            'resellers' => User::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int                       $regionId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, int $regionId) {
        $data = $request->validate([
            'region-name'    => 'required',
            'region-user-id' => 'required|integer',
            'region-zips'    => 'required|json',
        ]);

        $region     = Region::find($regionId);
        $regionZips = array_column(json_decode($data['region-zips']), 'value');

        // Frissítjük a két alap adatot
        $region->user_id = intval($data['region-user-id']);
        $region->name    = trim($data['region-name']);

        // Lekezeljük az irányítószámokat
        // - Kitöröljük a régieket...
        RegionZip::where('region_id', $regionId)->delete();

        // - Bejönnek az újak...
        $zipSuccess = 0;
        foreach ($regionZips as $zip) {
            if (RegionZip::where('zip', $zip)->first()) {
                continue;
            }
            $rZip            = new RegionZip();
            $rZip->region_id = $regionId;
            $rZip->zip       = $zip;

            if ($rZip->save()) {
                $zipSuccess++;
            }
        }

        if ($zipSuccess = count($regionZips)) {
            return redirect(url()->previous(action('RegionController@index')))->with([
                'success' => 'Régió sikeresen frissítve',
            ]);
        }

        return redirect(url()->previous(action('RegionController@index')))->with([
            'error' => 'Hiba történt a régió frissítésekor',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function destroy(Region $region) {
        //
    }

    public function generateByResellers() {
        Log::info('Régiók generálása a viszonteladók régi irányítószámai alapján');

        $users = User::whereHas('zips')->get();
        foreach ($users as $user) {
            echo $user->name.'<br>';
            $region          = new Region();
            $region->name    = $user->name.' régió';
            $region->user_id = $user->id;
            $region->save();

            foreach ($user->zips as $userZip) {
                $rz            = new RegionZip();
                $rz->region_id = $region->id;
                $rz->zip       = $userZip->zip;
                $rz->save();
            }

            Log::info(sprintf('Új régió létrehozva: %s (%s irányítószám)', $region->name, $region->zips()->count()));
        }

        if (Region::count() == count($users)) {
            Log::info('Az összes viszonteladóhoz létrejöttek a régiók');
        } else {
            Log::error('Nem jött létre minden viszonteladóhoz a régiója');
        }
    }
}
