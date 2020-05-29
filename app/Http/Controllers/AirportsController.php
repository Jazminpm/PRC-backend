<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AirportsController extends Controller
{
    public static function insertURL($json, $airport)
    {
        try {
            // Get airport_id: it depends of the iata and the country
            $json['airport_id'] = DB::table('airports')
                ->select('airports.id')
                ->join('cities as c', 'c.id', '=', 'airports.city_id')
                ->join('countries as co', 'c.country_id', '=', 'co.id')
                ->where('iata', $json['airport_id'])
                ->where('co.name', $airport)
                ->first()->id;

            DB::table('airports')->where('id', $json['airport_id'])
                ->update(array('airport_url' => $json['airport_url']));
        } catch (Throwable $e) {
            // Do nothing
        }

    }

    public static function getAirportURL($airport_id)
    {
        $url = DB::table('airports')
            ->select('airport_url')
            ->where('id', $airport_id)->first();
        if (is_null($url)) {
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
        if (is_null($data)) {
            return null;
        } else {
            return $data->icao;
        }
    }

    /**
     * @OA\GET(
     *      path="/api/airports/coordinates",
     *      operationId="getAirportsCoordinates",
     *      tags={"airports"},
     *      summary="Get all airports coordinates with URL",
     *      description="Returns all the airports coordinates and basic info.",
     *      @OA\Response(
     *          response=200,
     *          description="Ok.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          type="array",
     *                          @OA\Items(type="json"),
     *                          description="All coordinates"
     *                      ),
     *                      example={
     *                          {"airport_id": 216,"airport_name": "Cootamundra Airport","airport_country": "Australia","airport_city": "","airport_lon": 148.028,"airport_lat": -34.623901},
     *                          {"airport_id": 241,"airport_name": "Adelaide International Airport","airport_country": "Australia","airport_city": "Adelaide","airport_lon": 138.53101,"airport_lat": -34.945},
     *                      }
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
    public static function getAirportsCoordinates()
    {
        $data = DB::select(DB::raw("
                select a.id                         as airport_id,
                       a.name                       as airport_name,
                       c2.name                      as airport_country,
                       c.name                       as airport_city,
                       a.longitude                  as airport_lon,
                       a.latitude                   as airport_lat
                from airports a
                         join cities c on a.city_id = c.id
                         join countries c2 on c.country_id = c2.id
                where airport_url is not null
                order by c.id, airport_country, airport_id;"
        ));
        if (is_null($data)) {
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
        if (is_null($data)) {
            return null;
        } else {
            return response()->json($data, JsonResponse::HTTP_OK);
        }
    }

    /**
     * @OA\GET(
     *      path="/api/airports/flights/{id}",
     *      operationId="getAirportFlights",
     *      tags={"airports"},
     *      summary="Obtain the flights from an airport",
     *      description="Get all the flights related with the airport_id passed in the url.",
     *      @OA\Parameter(
     *          name="id",
     *          description="Airport id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Ok.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          type="route",@OA\Items(type="json"),
     *                          description="airport_id in the route"
     *                      ),
     *                      example={
     *                          {
     *                              "id": "U27206",
     *                              "airport_name": "Barcelona International Airport",
     *                              "airline_name": "EasyJet (DS)",
     *                              "date_time": "2020-05-26 22:55:00",
     *                              "status_name": "Cancelled",
     *                              "prediction": null
     *                          },
     *                          {
     *                          "id": "U26030",
     *                          "airport_name": "Barcelona International Airport",
     *                          "airline_name": "EasyJet (DS)",
     *                          "date_time": "2020-05-26 22:15:00",
     *                          "status_name": "Cancelled",
     *                          "prediction": null
     *                         },
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
     * @param Request $request
     * @return JsonResponse
     */
    public static function getAirportFlights(Request $request)
    {
        $validator = Validator::make(array("id" => $request->id), [
            'id' => ['required', 'integer', 'exists:airports,id'],
        ]);
        if ($validator->fails()) {
            return failValidation($validator);
        } else {
            $date = new DateTime('now');
            $todayStr = $date->format('Y-m-d H:i:s');
            $date = \Carbon\Carbon::today()->subDays(5);
            $dateStr = $date->format('Y-m-d H:i:s');

            $airport_id = $request->id; // obtengo el id introducido en la ruta

            $flights = DB::table('airports AS air')
                ->select('f.id', 'air.name AS airport_name', 'a.name AS airline_name', 'f.date_time', 'fs.name AS status_name', 'f.prediction')
                ->join('flights AS f', 'air.id', '=', 'f.airport_id')
                ->join('flight_statuses AS fs', 'f.delay', '=', 'fs.id')
                ->join('airlines AS a', 'f.airline_id', '=', 'a.id')
                ->where('f.date_time', '>=', $dateStr)
                ->where('air.id', '=', $airport_id)
                ->orderBy('date_time', 'asc')
                ->get();
            if (is_null($flights)) {
                return null;
            } else {
                return response()->json($flights, JsonResponse::HTTP_OK);
            }
        }
    }

    /**
     * @OA\GET(
     *      path="/api/airports/comments/{id}",
     *      operationId="getAirportComments",
     *      tags={"airports"},
     *      summary="Obtain the comments from an airport",
     *      description="Get all the comments related with the city of the airport passed in the url.",
     *      @OA\Parameter(
     *          name="id",
     *          description="Airport id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Ok.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          type="route",@OA\Items(type="json"),
     *                          description="airport_id in the route"
     *                      ),
     *                      example={
     *                          {"path": "/airports/flights/5327"}
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
     * @param Request $request
     * @return JsonResponse
     */
    public static function getAirportComments(Request $request)
    {
        $validator = Validator::make(array("id" => $request->id), [
            'id' => ['required', 'integer', 'exists:airports,id'],
        ]);
        if ($validator->fails()) {
            return failValidation($validator);
        } else {
            $airport_id = $request->id; // obtengo el id introducido en la ruta

            $comments = DB::table('airports AS air')
                ->select('air.name AS airport_name', 'ct.name AS city_name', 'c.place', 'c.title', 'c.date_time', 'c.message', 'c.grade')
                ->join('comments AS c', 'air.city_id', '=', 'c.city_id')
                ->join('cities AS ct', 'air.city_id', '=', 'ct.id')
                ->where('air.id', '=', $airport_id)
                ->orderBy('date_time', 'DESC')
                ->get();
            if (is_null($comments)) {
                return null;
            } else {
                return response()->json($comments, JsonResponse::HTTP_OK);
            }
        }
    }
    public static function getCityID($airport_id){
        $city_id = DB::table('airports')
            ->select('city_id')
            ->where('airports.id', '=', $airport_id)->first();
        if (is_null($city_id)) {
            return null;
        } else {
            return $city_id->city_id;
        }
    }
}
