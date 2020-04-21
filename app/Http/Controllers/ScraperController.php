<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Exception;

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
        $script = config('python.scripts') . 'scraper_1.py';
        $inserts = 0;
        foreach (executePython($script, $request) as $result) {
            $data = json_decode($result, true);
            WeatherController::insert($data);
            $inserts += 1;
        }
        return response()->json(["total" => $inserts], 200);
    }

    /**
     * @OA\Post(
     *      path="/api/scrapers/weathers/history",
     *      operationId="getWeathersHistory",
     *      tags={"scrapers"},
     *      summary="Tu Tiempo scraper",
     *      description="Launches Tu Tiempo scraper with the requested date.",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="date",
     *                      type="date",
     *                      description="Date in format Y-m-d"
     *                  ),
     *                  example={"date": "2020-04-19"}
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
            $script = config('python.scripts') . 'scraper_2.py';
            $inserts = 0;
            foreach (executePython($script, $request) as $result) {
                $data = json_decode($result, true);
                WeatherController::insert($data);
                $inserts += 1;
            }
            return response()->json(["total" => $inserts], JsonResponse::HTTP_OK);
        }
    }

    function flightsHistory(Request $request)
    {
        $validator = Validator::make($request->json()->all(), [
            'date' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:yesterday']
        ]);

        if ($validator->fails()) {
            return failValidation($validator);
        } else {
            $script = config('python.scripts') . 'scraper_3.py';
            $inserts = 0;
            foreach (executePython($script, $request) as $result) {
                $data = json_decode($result, true);
                FlightsController::insert($data);
                $inserts += 1;
            }
            return response()->json(["total" => $inserts], JsonResponse::HTTP_OK);
        }
    }

    function flightsForecast(Request $request)
    {
        $script = config('python.scripts') . 'scraper_5.py';
        $inserts = 0;
        foreach (executePython($script, $request) as $result) {
            $data = json_decode($result, true);
            FlightsController::insert($data);
            $inserts += 1;
        }
        return response()->json(["total" => $inserts], JsonResponse::HTTP_OK);
    }
}
