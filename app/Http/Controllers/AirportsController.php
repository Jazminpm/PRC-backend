<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class AirportsController extends Controller
{
    public static function insertURL($json, $airport)
    {
        try{
            // Get airport_id: it depends of the iata and the country
            $json['airport_id'] = DB::table('airports')
                ->select('airports.id')
                ->join('cities as c', 'c.id', '=', 'airports.city_id')
                ->join ('countries as co', 'c.country_id', '=', 'co.id')
                ->where('iata', $json['airport_id'])
                ->where('co.name', $airport)
                ->first()->id;

            DB::table('airports')->where('id', $json['airport_id'])
                ->update(array('airport_url' => $json['airport_url']));
        } catch (Throwable $e){
            // Do nothing
        }

    }

    public static function getAirportURL($airport_id)
    {
        $url = DB::table('airports')
            ->select('airport_url')
            ->where('id', $airport_id)->first();
        if (is_null($url)){
            return null;
        } else {
            return $url->airport_url;
        }
    }

    public static function getAirportIcao($arg)
    {
        $data = DB::table('airports')
            ->select('icao')
            ->where('id', $arg)->first();
        if (is_null($data)){
            return null;
        } else {
            return $data->icao;
        }
    }

    public static function getAirportsCoordinates()
    {
        $data = DB::table('airports')
            ->select(['longitude', 'latitude'])->whereNotNull('airport_url')->get()->toJson();
        if (is_null($data)){
            return null;
        } else {
            return response()->json(compact('data'), JsonResponse::HTTP_OK);
        }
    }
}
