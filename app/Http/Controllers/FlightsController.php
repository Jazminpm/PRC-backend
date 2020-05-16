<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
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
}
