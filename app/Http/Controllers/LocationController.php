<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    public function getCity(Request $request)
    {
        $city = DB::table('cities')->where('name', '=', $request->name)->first();
        return response()->json($city);
    }

    public function getCountry(Request $request)
    {
        $country = DB::table('countries')->where('name', '=', $request->name)->first();
        return response()->json($country);
    }
}
