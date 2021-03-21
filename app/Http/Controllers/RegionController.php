<?php

namespace App\Http\Controllers;

use App\Region;
use App\RegionZip;
use App\User;
use Illuminate\Http\Request;
use Log;

class RegionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index() {
        return view('region.index')->with([
            'regions' => Region::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create() {
        return view('region.create')->with([
            'resellers' => User::whereHas('zips')->get(),
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
     * @param  \App\Region  $region
     * @return \Illuminate\Http\Response
     */
    public function edit(Region $region) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Region               $region
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Region $region) {
        //
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
