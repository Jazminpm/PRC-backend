<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Throwable;

class FlightsController extends Controller
{
    /**
     * INSERT weather data if possible. UPDATE otherwise.
     *
     * @param $json
     */
    public static function insert($json)
    {
        // Get foreign key of airline and a city
        try {
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
        } catch (Throwable $e){
            dd($json);
        }

    }

    public static function getModelData($characteristic, $init_date, $final_date){
        // Data that exist in both tables (flights, weather)
        if (in_array("date_time", $characteristic)){
            $pos = array_keys($characteristic, "date_time")[0];
            $characteristic[$pos] = "flights.date_time";
        }

        if (in_array("airport_id", $characteristic)){
            $pos = array_keys($characteristic, "airport_id")[0];
            $characteristic[$pos] = "flights.airport_id";
        }

        if (in_array("date", $characteristic)){
            $pos = array_keys($characteristic, "date")[0];
            $characteristic[$pos] = DB::raw('DATE(flights.date_time) as date');
        }

        if (in_array("time", $characteristic)){
            $pos = array_keys($characteristic, "time")[0];
            $characteristic[$pos] = DB::raw('TIME(flights.date_time) as time');
        }

        return json_decode(DB::table('flights')->select($characteristic) //$columns
            ->join('weathers as w', 'w.airport_id', '=', 'flights.airport_id', 'right outer')
            ->where(DB::raw('DATE(flights.date_time)'), '>=', $init_date)
            ->where(DB::raw('DATE(flights.date_time)'), '<=', $final_date)
            ->where(DB::raw('DATE(w.date_time)'), DB::raw('DATE(flights.date_time)'))
            ->where(DB::raw('HOUR(w.date_time)'), DB::raw('HOUR(flights.date_time)'))
            ->where(DB::raw('(extract(minute from time(w.date_time) - time(flights.date_time)))'),'>=', '0')
            ->where(DB::raw('(extract(minute from time(w.date_time) - time(flights.date_time)))'),'<', '30')
            ->whereIn('delay', [0,1])
            ->get(), True);
    }
}
