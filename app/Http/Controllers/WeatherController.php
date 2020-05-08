<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class WeatherController extends Controller
{
    /**
     * INSERT weather data if possible. UPDATE otherwise.
     *
     * @param $json
     */
    public static function insert($json)
    {
        DB::table('weathers')->updateOrInsert([
            'date_time' => $json['date_time'],
            'airport_id' => $json['airport_id']
        ], $json);
    }
}
