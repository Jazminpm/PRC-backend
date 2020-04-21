<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class FlightsController extends Controller
{
    /**
     * INSERT weather data if possible. UPDATE otherwise.
     *
     * @param $json
     */
    public static function insert($json)
    {
        // Get foreign key of airline and
        $json['airline_id'] = DB::table('airlines')
            ->select('id')
            ->where('name', 'like', '%'.$json['airline_id'].'%')->first()->id;
        $json['city_id'] = DB::table('cities')
            ->select('id')
            ->where('name', 'like', '%'.$json['city_id'].'%')->first()->id;

        DB::table('flights')->updateOrInsert([
            'id' => $json['id'],
            'date_time' => $json['date_time'],
        ], $json);
    }
}
