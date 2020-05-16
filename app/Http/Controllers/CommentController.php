<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    /**
     * INSERT comments data if possible. UPDATE otherwise.
     *
     * @param $json
     */
    public static function insert($json)
    {
        # column => value
        DB::table('comments')->updateOrInsert([
            'original_message' => $json['original_message'],
            'date_time' => $json['date_time']
        ], $json);
    }

    public function getTopDestinations()
    {
        // QUERY: get best cities by grade average
        // select `c`.`name` as `city`, avg(com.grade) as grade, avg(com.sentiment) as sentiment
        // from `comments` as `com`
        //         inner join `cities` as `c` on `com`.`city_id` = `c`.`id`
        // group by `com`.`city_id`
        // order by `grade` desc
        // limit 4;
        $data = DB::table('comments', 'com')
            ->select(['c.name as city', DB::raw('avg(com.grade) as grade, avg(com.sentiment) as sentiment')])
            ->join('cities as c', 'com.city_id', '=', 'c.id')
            ->groupBy('com.city_id')->orderBy('grade', 'desc')->limit(4)->get();
        // todo: grade se devuelve como string. Hay que solucionarlo
        return response()->json(compact('data'), JsonResponse::HTTP_OK);
    }
}
