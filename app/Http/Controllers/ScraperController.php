<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScraperController extends Controller
{
    function scrapers(Request $request)
    {
        $id = $request->id;
        $script = config('python.scripts') . 'scraper_' . $id . '.py';
        foreach (executePython($script, $request) as $result) {
            $data = json_decode($result, true);

            if ($id == 1 or $id == 2) {
                WeatherController::insert($data);
            }
        }

        return response('Execute complete', 200);
    }

    /**
     * @OA\Post(
     *      path="/api/scrapers/weathers/forecast",
     *      operationId="getWeathersForecast",
     *      tags={"scrapers"},
     *      summary="El Tiempo scraper",
     *      description="Launches El Tiempo scraper for tomorrow's forecast data",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="airport_id",
     *                      type="int",
     *                      description="Departure airport id"
     *                  ),
     *                  example={"airport_id": 5306}
     *              )
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
     *                          property="total",
     *                          type="integer",
     *                          description="Total inserted documents"
     *                      ),
     *                      example={
     *                          "total": 24
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *      @OA\Response(response=400, description="Bad request"),
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
     *                          description="Line that thorws the execption."
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
    function weatherForecast(Request $request)
    {
        $icao = AirportsController::getAirportIcao($request->airport_id);
        if (is_null($icao)){
            return response()->json(["errors" => 'The airport does not have ICAO.'],
                JsonResponse::HTTP_BAD_REQUEST);
        } else {
            $args = $request->all();
            array_push($args, $icao);

            $script = config('python.scripts') . 'scraper_forecast_weather.py';
            $inserts = 0;
            foreach (executePython($script, $args) as $result) {
                $data = json_decode($result, true);
                WeatherController::insert($data);
                $inserts += 1;
            }
            return response()->json(["total" => $inserts], 200);
        }
    }
    /*
    function weatherForecast(Request $request)
    {
        $script = config('python.scripts') . 'scraper_1.py';
        $inserts = 0;
        foreach (executePython($script, $request) as $result) {
            $data = json_decode($result, true);
            WeatherController::insert($data);
            $inserts += 1;
        }
        return response()->json(["total" => $inserts], 200);
    }*/

    /**
     * @OA\Post(
     *      path="/api/scrapers/weathers/history",
     *      operationId="getWeathersHistory",
     *      tags={"scrapers"},
     *      summary="Tu Tiempo scraper",
     *      description="Launches Tu Tiempo scraper with the requested date and departure airport id.",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="date",
     *                      type="date",
     *                      description="Date in format Y-m-d"
     *                  ),
     *                  @OA\Property(
     *                      property="airport_id",
     *                      type="int",
     *                      description="Departure airport id"
     *                  ),
     *                  example={"date":"2020-04-20", "airport_id": 5306}
     *              )
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
     *                          property="total",
     *                          type="integer",
     *                          description="Total inserted documents"
     *                      ),
     *                      example={
     *                          "total": 47
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="errors",
     *                          type="array",
     *                          description="List of errors.",
     *                          @OA\Items(type="string")
     *                      ),
     *                      example={
     *                          "errors": {
     *                              "The date field is required.",
     *                              "The date is not a valid date.",
     *                              "The date does not match the format Y-m-d.",
     *                              "The date must be a date before or equal to yesterday."
     *                          }
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
     *                          description="Line that thorws the execption."
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
    function weatherHistory(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'date' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:yesterday']
        ]);

        if ($validator->fails()) {
            return failValidation($validator);
        } else {
            $icao = AirportsController::getAirportIcao($request->airport_id);
            if (is_null($icao)){
                return response()->json(["errors" => 'The airport does not have ICAO.'],
                    JsonResponse::HTTP_BAD_REQUEST);
            } else {
                $args = $request->all();
                array_push($args, $icao);

                $script = config('python.scripts') . 'scraper_2.py';
                $inserts = 0;
                foreach (executePython($script, $args) as $result) {
                    $data = json_decode($result, true);
                    WeatherController::insert($data);
                    $inserts += 1;
                }
                return response()->json(["total" => $inserts], JsonResponse::HTTP_OK);
            }
        }
    }
    /**
     * @OA\Post(
     *      path="/api/scrapers/flights/history",
     *      operationId="getFlightsHistory",
     *      tags={"scrapers"},
     *      summary="Airportia historical scraper",
     *      description="Launches Airportia historical scraper with the requested date and with the departure airport id.",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="date",
     *                      type="date",
     *                      description="Date in format Y-m-d"
     *                  ),
     *                  @OA\Property(
     *                      property="airport_id",
     *                      type="int",
     *                      description="Departure airport id"
     *                  ),
     *                  example={"date":"2020-04-12", "airport_id":5327}
     *              )
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
     *                          property="total",
     *                          type="integer",
     *                          description="Total inserted documents"
     *                      ),
     *                      example={
     *                          "total": 47
     *                      }
     *                  )
     *              )
     *          }
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="errors",
     *                          type="array",
     *                          description="List of errors.",
     *                          @OA\Items(type="string")
     *                      ),
     *                      example={
     *                          "errors": {
     *                              "The date field is required.",
     *                              "The date is not a valid date.",
     *                              "The date does not match the format Y-m-d.",
     *                              "The date must be a date before or equal to yesterday."
     *                          }
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
     *                          description="Line that thorws the execption."
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
    function flightsHistory(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'date' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:yesterday']
        ]);

        if ($validator->fails()) {
            return failValidation($validator);
        } else {
            $url = AirportsController::getAirportURL($request->airport_id);
            if (is_null($url)){
                return response()->json(["errors" => 'The airport does not have url.'],
                    JsonResponse::HTTP_BAD_REQUEST);
            } else {
                $args = $request->all();
                array_push($args, $url);
                $script = config('python.scripts') . 'scraper_3.py';

                $inserts = 0;
                foreach (executePython($script, $args) as $result) {
                    $data = json_decode($result, true);
                    FlightsController::insert($data);
                    $inserts += 1;
                }
                return response()->json(["total" => $inserts], JsonResponse::HTTP_OK);
            }
        }
    }
    /**
     * @OA\Post(
     *      path="/api/scrapers/flights/forecast",
     *      operationId="getFlightsForecast",
     *      tags={"scrapers"},
     *      summary="Airportia forecast scraper",
     *      description="Launches Airportia forecast scraper with the requested departure airport id.",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="airport_id",
     *                      type="int",
     *                      description="Departure airport id"
     *                  ),
     *                  example={"airport_id":5306}
     *              )
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
     *                          property="total",
     *                          type="integer",
     *                          description="Total inserted documents"
     *                      ),
     *                      example={
     *                          "total": 47
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
     *                          description="Line that thorws the execption."
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
    function flightsForecast(Request $request)
    {
        $url = AirportsController::getAirportURL($request->airport_id);
        if (is_null($url)){
            return response()->json(["errors" => 'The airport does not have url.'],
                JsonResponse::HTTP_BAD_REQUEST);
        } else {
            $args = $request->all();
            array_push($args, $url);

            $script = config('python.scripts') . 'scraper_5.py';
            $inserts = 0;
            foreach (executePython($script, $args) as $result) {
                $data = json_decode($result, true);
                FlightsController::insert($data);
                $inserts += 1;
            }
            return response()->json(["total" => $inserts], JsonResponse::HTTP_OK);
        }
    }
    /**
     * @OA\Post(
     *      path="/api/scrapers/airportia/url",
     *      operationId="setAirportUrl",
     *      tags={"scrapers"},
     *      summary="Add the url of an Airportia airport to the database.",
     *      description="Launches URL Airportia scraper with the requested country id.",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="country_id",
     *                      type="int",
     *                      description="Id of a country in the database."
     *                  ),
     *                  example={"country_id":199}
     *              )
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
     *                          property="total",
     *                          type="integer",
     *                          description="Total inserted documents"
     *                      ),
     *                      example={
     *                          "total": 20
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
     *                          description="Line that thorws the execption."
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
    function airportUrl(Request $request)
    {
        $name = CountriesController::getCountryName($request->country_id);
        if (is_null($name)){
            return response()->json(["errors" => 'The airport does not have url.'],
                JsonResponse::HTTP_BAD_REQUEST);
        } else {
            $args = $request->all();
            $args['country_id'] = $name;

            $script = config('python.scripts') . 'scraper_6.py';
            $inserts = 0;
            foreach (executePython($script, $args) as $result) {
                $data = json_decode($result, true);
                AirportsController::insertURL($data, $args['country_id']);
                $inserts += 1;
            }
            return response()->json(["total" => $inserts], JsonResponse::HTTP_OK);
        }
    }
}
