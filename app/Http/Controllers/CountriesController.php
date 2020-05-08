<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class CountriesController extends Controller
{
    public static function getCountryName($country_id)
    {
        $url = DB::table('countries')
            ->select('name')
            ->where('id', $country_id)->first();
        if (is_null($url)){
            return null;
        } else {
            return $url->name;
        }
    }
}
