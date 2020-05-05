<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CitiesController extends Controller
{
    public static function getCityName($city_id)
    {
        $name = DB::table('cities')
            ->select('name')
            ->where('id', $city_id)->first();
        if (is_null($name)){
            return null;
        } else {
            return $name->name;
        }
    }
}

