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

    // todo: API documentation
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

    // todo: API documentation
    public static function getAirportsCoordinates()
    {
        $data = DB::table('airports')
            ->select(['longitude', 'latitude'])->whereNotNull('airport_url')->get();
        if (is_null($data)){
            return null;
        } else {
            return response()->json(compact('data'), JsonResponse::HTTP_OK);
        }
    }

    /**
     * @OA\GET(
     *      path="/api/airports/airports",
     *      operationId="getAirports",
     *      tags={"airports"},
     *      summary="Get all airports with URL",
     *      description="Returns all the airports that have an URL.",
     *      @OA\Response(
     *          response=200,
     *          description="Ok.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          type="array",@OA\Items(type="json"),
     *                          description="All models"
     *                      ),
     *                      example={
     *                          {"id": 2286,"name": "Agen-La Garenne Airport"},
     *                          {"id": 2289,"name": "Ajaccio-Napoléon Bonaparte Airport"},
     *                          {"id": 2306,"name": "Aurillac Airport"},
     *                          {"id": 2312,"name": "Bastia-Poretta Airport"},
     *                          {"id": 2314,"name": "Paris Beauvais Tillé Airport"}
     *                    }
     *                  )
     *              )
     *          }
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="message",
     *                          type="string",
     *                          description="Server message that contains the error."
     *                      ),
     *                      @OA\Property(
     *                          property="exception",
     *                          type="string",
     *                          description="Generated exception."
     *                      ),
     *                      @OA\Property(
     *                          property="file",
     *                          type="string",
     *                          description="File that throw the exception."
     *                      ),
     *                      @OA\Property(
     *                          property="line",
     *                          type="integer",
     *                          description="Line that throws the exception."
     *                      ),
     *                      @OA\Property(
     *                          property="trace",
     *                          type="array",
     *                          description="Trace route objects.",
     *                          @OA\Items(type="object")
     *                      ),
     *                      example={
     *                          "messagge": "The command failed.",
     *                          "exception": "",
     *                          "file": "",
     *                          "line": 150,
     *                          "trace": {"file":"", "line":1, "content":""}
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *  )
     *
     * @return JsonResponse
     */
    public static function getAirports()
    {
        $data = DB::table('airports')
            ->select(['id', 'name'])->whereNotNull('airport_url')->get();
        if (is_null($data)){
            return null;
        } else {
            return response()->json($data, JsonResponse::HTTP_OK);
        }
    }

    // todo: API documentation
    public static function getAirportsPreview()
    {
        $data = DB::select(DB::raw(
            "
            select arp.id                                                 as airport_id,
                   arp.name                                               as airport_name,
                   arp.longitude                                          as airport_lon,
                   arp.latitude                                           as airport_lat,
                   intermediare.flight_id                                 as fligth_id,
                   DATE_FORMAT(intermediare.schedulated_date, '%Y-%m-%d') as schedulated_date
            from airports arp
                     inner join
                 (select f.id        as flight_id,
                         f.date_time as schedulated_date,
                         fs.name     as flight_statis
                  from flights f
                           join flight_statuses fs on f.delay = fs.id
                  where DATE_FORMAT(f.date_time, '%Y-%m-%d') = CURDATE()
                  order by f.date_time desc
                  limit 5) intermediare
            order by airport_id, schedulated_date;"
        ));
        if (is_null($data)){
            return null;
        } else {
            return response()->json(compact('data'), JsonResponse::HTTP_OK);
        }
    }
}
