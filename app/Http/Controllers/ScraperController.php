<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ScraperController extends Controller
{
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
     *     @OA\Response(
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
     *                              "The airport id field is required.",
     *                              "The airport id must be an integer.",
     *                              "The selected airport id is invalid.",
     *                              "The airport does not have ICAO."
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
      *     security={
      *         {"bearer": {}}
      *     }
     *  )
     *
     * @param Request $request
     * @return JsonResponse
     */
    function weatherForecast(Request $request)
    {
        $date = new DateTime('now');
        $dateStr = $date->format('Y-m-d H:i:s');

        $validator = Validator::make($request->json()->all(), [
            'airport_id' => ['required', 'integer', 'exists:airports,id']
        ]);

        if ($validator->fails()) {
            $validation = failValidation($validator);
            $message = "The weather forecast scraper launched at ".$dateStr." did not finished.
            The errors have been:";
            MailController::emailErrors($validation, $dateStr, $message);
            return $validation;
        } else {
            $icao = AirportsController::getAirportIcao($request->airport_id);
            if (is_null($icao)){
                $message = "The weather history scraper launched did not finished. Error: The airport does not have ICAO.";
                MailController::sendMailScrapers($dateStr, $message ,'Scraper failed');
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

                $airportName = DB::table('airports')->select(['name'])->where('id', $request->airport_id)->first()->name;
                $message = "The weather forecast scraper launched at ".$dateStr." from the ".$airportName." airport has already finished.";
                MailController::sendMailScrapers($date, $message,'Scraper finished');

                return response()->json(["total" => $inserts], 200);
            }
        }
    }

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
     *                              "The date must be a date before or equal to yesterday.",
     *                              "The airport id field is required.",
     *                              "The airport id must be an integer.",
     *                              "The selected airport id is invalid.",
     *                              "The airport does not have ICAO."
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
     *     security={
     *         {"bearer": {}}
     *     }
     *  )
     *
     * @param Request $request
     * @return JsonResponse
     */
    function weatherHistory(Request $request)
    {
        $date = new DateTime('now');
        $dateStr = $date->format('Y-m-d H:i:s');
        $validator = Validator::make($request->json()->all(), [
            'date' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:yesterday'],
            'airport_id' => ['required', 'integer', 'exists:airports,id']
        ]);

        if ($validator->fails()) {
            $validation = failValidation($validator);
            $message = "The weather history scraper launched at ".$dateStr." did not finished.
            The errors have been: ";
            MailController::emailErrors($validation, $dateStr, $message);
            return $validation;
        } else {
            $icao = AirportsController::getAirportIcao($request->airport_id);
            if (is_null($icao)){
                $message = "The weather history scraper launched did not finished. Error: The airport does not have ICAO.";
                MailController::sendMailScrapers($dateStr, $message ,'Scraper failed');
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

                $airportName = DB::table('airports')->select(['name'])->where('id', $request->airport_id)->first()->name;
                $message = "The weather history scraper launched at ".$dateStr." from the ".$airportName." airport has already finished.";
                MailController::sendMailScrapers($date, $message,'Scraper finished');
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
     *                              "The date must be a date before or equal to yesterday.",
     *                              "The airport id field is required.",
     *                              "The airport id must be an integer.",
     *                              "The selected airport id is invalid.",
     *                              "The airport does not have url."
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
     *     security={
     *         {"bearer": {}}
     *     }
     *  )
     *
     * @param Request $request
     * @return JsonResponse
     */
    function flightsHistory(Request $request)
    {
        $date = new DateTime('now');
        $dateStr = $date->format('Y-m-d H:i:s');
        $validator = Validator::make($request->json()->all(), [
            'date' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:yesterday'],
            'airport_id' => ['required', 'integer', 'exists:airports,id']
        ]);

        if ($validator->fails()) {
            $validation = failValidation($validator);
            $message = "The flight history scraper launched at ".$dateStr." did not finished.
            The errors have been: ";
            MailController::emailErrors($validation, $dateStr, $message);
            return $validation;
        } else {
            $url = AirportsController::getAirportURL($request->airport_id);
            if (is_null($url)){
                $message = "The flight forecast scraper launched did not finished. Error: The airport does not have url.";
                MailController::sendMailScrapers($dateStr, $message ,'Scraper failed');
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

                $airportName = DB::table('airports')->select(['name'])->where('id', $request->airport_id)->first()->name;
                $message = "The flight history scraper launched at ".$dateStr." from the ".$airportName." airport has already finished.";
                MailController::sendMailScrapers($date, $message,'Scraper finished');
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
     *     @OA\Response(
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
     *                              "The airport id field is required.",
     *                              "The airport id must be an integer.",
     *                              "The selected airport id is invalid.",
     *                              "The airport does not have url."
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
    function flightsForecast(Request $request)
    {
        $date = new DateTime('now');
        $dateStr = $date->format('Y-m-d H:i:s');
        $validator = Validator::make($request->json()->all(), [
            'airport_id' => ['required', 'integer', 'exists:airports,id']
        ]);

        if ($validator->fails()) {
            $validation = failValidation($validator);
            $message = "The flight forecast scraper launched at ".$dateStr." did not finished.
            The errors have been: ";
            MailController::emailErrors($validation, $dateStr, $message);
            return $validation;
        } else {
            $url = AirportsController::getAirportURL($request->airport_id);
            if (is_null($url)){
                $message = "The flight forecast scraper launched did not finished. Error: The airport does not have url.";
                MailController::sendMailScrapers($dateStr, $message ,'Scraper failed');
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

                $airportName = DB::table('airports')->select(['name'])->where('id', $request->airport_id)->first()->name;
                $message = "The flight forecast scraper launched at ".$dateStr." from the ".$airportName." airport has already finished.";
                MailController::sendMailScrapers($date, $message,'Scraper finished');
                return response()->json(["total" => $inserts], JsonResponse::HTTP_OK);
            }
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
     *     @OA\Response(
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
     *                              "The country id field is required.",
     *                              "The country id must be an integer.",
     *                              "The selected country id is invalid.",
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
     *     security={
     *         {"bearer": {}}
     *     }
     *  )
     *
     * @param Request $request
     * @return JsonResponse
     */
    function airportUrl(Request $request)
    {
        $date = new DateTime('now');
        $dateStr = $date->format('Y-m-d H:i:s');
        $validator = Validator::make($request->json()->all(), [
            'country_id' => ['required', 'integer', 'exists:countries,id']
        ]);

        if ($validator->fails()) {
            $validation = failValidation($validator);
            $message = "The Airport URL scraper launched at ".$dateStr." did not finished.
            The errors have been: ";
            MailController::emailErrors($validation, $dateStr, $message);
            return $validation;
        } else {
            $name = CountriesController::getCountryName($request->country_id);
            $args = $request->all();
            $args['country_id'] = $name;

            $script = config('python.scripts') . 'scraper_6.py';
            $inserts = 0;
            foreach (executePython($script, $args) as $result) {
                $data = json_decode($result, true);
                AirportsController::insertURL($data, $args['country_id']);
                $inserts += 1;
            }

            $countryName = DB::table('countries')->select(['name'])->where('id', $request->country_id)->first()->name;
            $message = "The URL Airportia scraper launched at ".$dateStr." from the country ".$countryName." has already finished.";
            MailController::sendMailScrapers($date, $message,'Scraper finished');
            return response()->json(["total" => $inserts], JsonResponse::HTTP_OK);
        }
    }
    /**
     * @OA\Post(
     *      path="/api/scrapers/comments",
     *      operationId="getComments",
     *      tags={"scrapers"},
     *      summary="Get comments from TripAdvisor",
     *      description="Gets all the comments from tripadvisor based on a query/search that will be a city id",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="city_id",
     *                      type="integer",
     *                      description="City"
     *                  ),
     *                  example={{"city_id": 5049}}
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
     *                              "The city id field is required.",
     *                              "The city id must be an integer.",
     *                              "The selected city id is invalid.",
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
     *     security={
     *         {"bearer": {}}
     *     }
     *  )
     *
     * @param Request $request
     * @return JsonResponse
     */
    function comments(Request $request)  # incluir analisis del sentimiento a cada comentario
    {
        $date = new DateTime('now');
        $dateStr = $date->format('Y-m-d H:i:s');
        $validator = Validator::make($request->json()->all(), [
            'city_id' => ['required', 'integer', 'exists:cities,id']
        ]);

        if ($validator->fails()) {
            $validation = failValidation($validator);
            $message = "The comments scraper launched at ".$dateStr." did not finished.
            The errors have been: ";
            MailController::emailErrors($validation, $dateStr, $message);
            return $validation;
        } else {
            $name = CitiesController::getCityName($request->city_id);
            if (is_null($name)) {
                $message = "The comments scraper launched did not finished. Error: The id does not have a city associated.";
                MailController::sendMailScrapers($dateStr, $message ,'Scraper failed');
                return response()->json(["errors" => 'The id does not have a city associated.'],
                    JsonResponse::HTTP_BAD_REQUEST);
            } else {
                $args = $request->all();
                array_push($args, $name);

                $script = config('python.scripts') . 'scraper_tripadvisor.py';
                $inserts = 0;
                //dd(executePython($script, $args));
                foreach (executePython($script, $args) as $result) {
                    $data = json_decode($result, true);
                    $data['city_id'] = $request->city_id;
                    $data['user_id'] = 2;
                    CommentController::insert($data);
                    $inserts += 1;
                }

                $cityName = DB::table('cities')->select(['name'])->where('id', $request->city_id)->first()->name;
                $message = "The comments scraper launched at ".$dateStr." from the city ".$cityName." has already finished.";
                MailController::sendMailScrapers($date, $message,'Scraper finished');
                return response()->json(["total" => $inserts], JsonResponse::HTTP_OK);
            }
        }
    }
}

