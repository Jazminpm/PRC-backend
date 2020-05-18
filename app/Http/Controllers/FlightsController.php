<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
                ->where('name', 'like', '%' . $json['airline_id'] . '%')->first()->id;

            $json['city_id'] = DB::table('cities')
                ->select('id')
                ->where('name', 'like', '%' . $json['city_id'] . '%')->first()->id;


            DB::table('flights')->updateOrInsert([
                'id' => $json['id'],
                'date_time' => $json['date_time'],
            ], $json);
        } catch (Throwable $e) {
            // dd($json);
        }

    }

    public static function getModelDataTrain($characteristic, $init_date, $final_date)
    {
        $characteristic = FlightsController::prepareCharacteristic($characteristic);

        return json_decode(DB::table('flights')->select($characteristic) //$columns
        ->join('weathers as w', 'w.airport_id', '=', 'flights.airport_id', 'right outer')
            ->where(DB::raw('DATE(flights.date_time)'), '>=', $init_date)
            ->where(DB::raw('DATE(flights.date_time)'), '<=', $final_date)
            ->where(DB::raw('DATE(w.date_time)'), DB::raw('DATE(flights.date_time)'))
            //->where(DB::raw('(extract(minute from time(w.date_time) - time(flights.date_time)))'),'>=', '0')
            //->where(DB::raw('(extract(minute from time(w.date_time) - time(flights.date_time)))'),'<', '60')
            ->where(DB::raw('HOUR(w.date_time)'), DB::raw('HOUR(flights.date_time)'))
            ->whereIn('delay', [0, 1])
            ->get(), True);
    }

    public static function prepareCharacteristic($characteristic)
    {
        // Data that exist in both tables (flights, weather)
        if (in_array("date_time", $characteristic)) {
            $pos = array_keys($characteristic, "date_time")[0];
            $characteristic[$pos] = "flights.date_time";
        }

        if (in_array("date", $characteristic)) {
            $pos = array_keys($characteristic, "date")[0];
            $characteristic[$pos] = DB::raw('DATE(flights.date_time) as date');
        }

        if (in_array("time", $characteristic)) {
            $pos = array_keys($characteristic, "time")[0];
            $characteristic[$pos] = DB::raw('TIME(flights.date_time) as time');
        }

        if (in_array("airport_id", $characteristic)) {
            $pos = array_keys($characteristic, "airport_id")[0];
            $characteristic[$pos] = "flights.airport_id";
        }
        return $characteristic;
    }

    public static function getModelDataPredict($characteristic, $init_date, $final_date, $airports)
    {
        $characteristic = FlightsController::prepareCharacteristic($characteristic);
        return json_decode(DB::table('flights')->select($characteristic) //$columns
        ->join('weathers as w', 'w.airport_id', '=', 'flights.airport_id', 'right outer')
            ->where(DB::raw('DATE(flights.date_time)'), '>=', $init_date)
            ->where(DB::raw('DATE(flights.date_time)'), '<=', $final_date)
            ->where(DB::raw('DATE(w.date_time)'), DB::raw('DATE(flights.date_time)'))
            ->whereIn('flights.airport_id', $airports)
            ->where(DB::raw('HOUR(w.date_time)'), DB::raw('HOUR(flights.date_time)'))
            ->whereIn('delay', [0, 1])
            ->get(), True);
    }


    public static function updatePrediction($data)
    {
        DB::table('flights')
            ->where('id', $data['id'])
            ->where('date_time', $data['date'] . ' ' . $data['time'])
            ->update(array('prediction' => $data['prediction']));
    }

    public function getDailyStats()
    {
        // QUERY: select in_time, delayed and cancelled flights for the current date
        // select `fs`.`name` as `status`, COUNT(fl.delay) AS daily_count
        // from `flights` as `fl`
        //          inner join `flight_statuses` as `fs` on `fs`.`id` = `fl`.`delay`
        // where fl.delay >= 0
        //     AND fl.delay <= 2
        //     AND DATE_FORMAT(fl.date_time, '%Y-%m-%d') = CURDATE()
        // group by `fl`.`delay`
        $data = DB::table('flights', 'fl')
            ->select(['fs.name AS status', DB::raw('COUNT(fl.delay) AS daily_count')])
            ->join('flight_statuses as fs', 'fs.id', '=', 'fl.delay')
            ->groupBy('fl.delay')
             ->whereRaw('fl.delay >= 0 AND fl.delay <= 2 AND DATE_FORMAT(fl.date_time, \'%Y-%m-%d\') = CURDATE()')
            ->get();
        return response()->json(compact('data'), JsonResponse::HTTP_OK);
    }

    /**
     * @OA\POST(
     *      path="/api/flights/groupFlights",
     *      operationId="getGroupFlights",
     *      tags={"flights"},
     *      summary="Get flights data (Group by)",
     *      description="Returns flights grouped between the date given by days, weeks, months or years.",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="start_date",
     *                      type="date",
     *                      description="Date in format Y-m-d"
     *                  ),
     *                  @OA\Property(
     *                      property="end_date",
     *                      type="date",
     *                      description="Date in format Y-m-d"
     *                  ),
     *                  example={"start_date":"2019-06-10", "end_date":"2020-05-09"}
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
     *                          type="array",@OA\Items(type="json"),
     *                          description="All models"
     *                      ),
     *                      example={
     *                          {"groupDate":"2019\/10","delay":0,"countDelay":4809,"prediction":null,"countPrediction":0},
     *                          {"groupDate":"2019\/10","delay":1,"countDelay":6180,"prediction":null,"countPrediction":0},
     *                          {"groupDate":"2019\/11","delay":0,"countDelay":6250,"prediction":null,"countPrediction":0},
     *                          {"groupDate":"2019\/12","delay":0,"countDelay":4965,"prediction":null,"countPrediction":0},
     *                          {"groupDate":"2019\/12","delay":1,"countDelay":8567,"prediction":null,"countPrediction":0},
     *                          {"groupDate":"2019\/6","delay":0,"countDelay":3988,"prediction":null,"countPrediction":0},
     *                          {"groupDate":"2019\/6","delay":1,"countDelay":6941,"prediction":null,"countPrediction":0},
     *                          {"groupDate":"2019\/7","delay":0,"countDelay":5847,"prediction":null,"countPrediction":0},
     *                          {"groupDate":"2019\/7","delay":1,"countDelay":10692,"prediction":null,"countPrediction":0},
     *                          {"groupDate":"2019\/8","delay":0,"countDelay":6141,"prediction":null,"countPrediction":0},
     *                    }
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
     *                              "The start date field is required.",
     *                              "The start date is not a valid date.",
     *                              "The start date does not match the format Y-m-d.",
     *                              "The start date must be a date before or equal to today.",
     *                              "The end date field is required.",
     *                              "The end date is not a valid date.",
     *                              "The end date does not match the format Y-m-d.",
     *                              "The end date must be a date before or equal to today.",
     *                              "There is no data for the specified date.",
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
    public function getGroupFlights(Request $request){
        $validator = Validator::make($request->json()->all(), [
            'start_date' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
            'end_date' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:today']
        ]);

        if ($validator->fails()) {
            return failValidation($validator);
        } else {
            $to = Carbon::createFromFormat('Y-m-d', $request->end_date);
            $from = Carbon::createFromFormat('Y-m-d', $request->start_date);
            $diff_in_days = $to->diffInDays($from);

            if ($diff_in_days <= 7) { // GROUP BY DAYS
                $result = DB::table('flights')->
                select(DB::raw('date(date_time) as groupDate'), 'delay', DB::raw('count(delay) as countDelay'),
                    'prediction', DB::raw('count(prediction) as countPrediction')) //$columns
                ->where(DB::raw('DATE(flights.date_time)'), '>=', $from)
                    ->where(DB::raw('DATE(flights.date_time)'), '<=', $to)
                    ->whereIn('delay', [0, 1, 2])
                    ->groupBy(DB::raw('date(date_time), delay, prediction'))
                    ->get();
            } else if ($diff_in_days > 7 && $diff_in_days <= 31) { // GROUP BY WEEKS
                $result = DB::table('flights')->
                select(DB::raw('CONCAT(YEAR(date_time), \'/\', WEEK(date_time)) as groupDate'), 'delay',
                    DB::raw('count(delay) as countDelay'), 'prediction', DB::raw('count(prediction) as countPrediction')) //$columns
                ->where(DB::raw('DATE(flights.date_time)'), '>=', $from)
                    ->where(DB::raw('DATE(flights.date_time)'), '<=', $to)
                    ->whereIn('delay', [0, 1, 2])
                    ->groupBy(DB::raw('CONCAT(YEAR(date_time), \'/\', WEEK(date_time)), delay, prediction'))
                    ->get();
            } else if ($diff_in_days > 31 && $diff_in_days <= 365) { // GROUP BY MONTHS
                $result = DB::table('flights')->
                select(DB::raw('CONCAT(YEAR(date_time), \'/\', MONTH(date_time)) as groupDate'),
                    'delay', DB::raw('count(delay) as countDelay'), 'prediction', DB::raw('count(prediction) as countPrediction')) //$columns
                ->where(DB::raw('DATE(flights.date_time)'), '>=', $from)
                    ->where(DB::raw('DATE(flights.date_time)'), '<=', $to)
                    ->whereIn('delay', [0, 1, 2])
                    ->groupBy(DB::raw('CONCAT(YEAR(date_time), \'/\', MONTH(date_time)), delay, prediction'))
                    ->get();
            } else { // Group by year
                $result = DB::table('flights')->
                select(DB::raw('YEAR(date_time) as groupDate'), 'delay', DB::raw('count(delay) as countDelay'),
                    'prediction', DB::raw('count(prediction) as countPrediction')) //$columns
                ->where(DB::raw('DATE(flights.date_time)'), '>=', $from)
                    ->where(DB::raw('DATE(flights.date_time)'), '<=', $to)
                    ->whereIn('delay', [0, 1, 2])
                    ->groupBy(DB::raw('YEAR(date_time), delay, prediction'))
                    ->get();
            }

            if (is_null($result)) {
                return response()->json(["errors" => 'There is no data for the specified date.'],
                    JsonResponse::HTTP_BAD_REQUEST);
            } else {
                return response()->json($result, JsonResponse::HTTP_OK);
            }
        }
    }
}
