<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        // select `c`.`name` as `city`, avg(com.grade)*2 as grade, avg(com.sentiment) as sentiment
        // from `comments` as `com`
        //         inner join `cities` as `c` on `com`.`city_id` = `c`.`id`
        // group by `com`.`city_id`
        // order by `sentiment` desc
        // limit 4;
        $data = DB::table('comments', 'com')
            ->select(['c.name as city', DB::raw('avg(com.grade)*2 as grade, avg(com.sentiment) as sentiment')])
            ->join('cities as c', 'com.city_id', '=', 'c.id')
            ->groupBy('com.city_id')->orderBy('sentiment', 'desc')->limit(4)->get();
        // todo: grade se devuelve como string. Hay que solucionarlo
        return response()->json(compact('data'), JsonResponse::HTTP_OK);
    }

    /**
     * @OA\GET(
     *      path="/api/cities/cities",
     *      operationId="getCities",
     *      tags={"comments"},
     *      summary="Get cities with comment",
     *      description="Returns all the cities that have a comment.",
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
     *                          {"city": "Paris","city_id": 2296},
     *                          {"city": "Pisa","city_id": 3336},
     *                          {"city": "Barcelona","city_id": 5029},
     *                          {"city": "Burgos","city_id": 5031},
     *                          {"city": "Madrid","city_id": 5049}
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
    public function getCities(){
        $data = DB::table('comments', 'com')
            ->select(['c.name as city', 'com.city_id'])
            ->join('cities as c', 'com.city_id', '=', 'c.id')
            ->groupBy('com.city_id')->get();
        return response()->json($data, JsonResponse::HTTP_OK);
    }

    /**
     * @OA\POST(
     *      path="/api/cities/data",
     *      operationId="getCityData",
     *      tags={"comments"},
     *      summary="Get polarity and grade",
     *      description="Returns the sentiment, polarity and the grades of all comments from a specific city.",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="city_id",
     *                      type="integer",
     *                  ),
     *                  example={"city_id":5049}
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
     *                            {"polarity": "0.78","sentiment": "0.91428571428571","grade": "5.00"},
     *                            {"polarity": "0.53","sentiment": "0.77","grade": "5.00"},
     *                            {"polarity": "0.62","sentiment": "0.7875","grade": "5.00"}
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
    public function getCityData(Request $request){
        $data = DB::table('comments')
            ->select(['polarity', 'sentiment', 'grade'])
            ->where('city_id', $request->city_id)->get();
        return response()->json($data, JsonResponse::HTTP_OK);
    }
}
