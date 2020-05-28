<?php

namespace App\Http\Controllers;

use App\Models\Comments\Comment;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

    /**
     * @OA\GET(
     *      path="/api/cities/top",
     *      operationId="getAirportsCoordinates",
     *      tags={"comments", "cities"},
     *      summary="Top 4 cities by user sentients",
     *      description="Returns the top 4 destinations in base of the sentiment average",
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
     *                          {"city": "Madrid","grade": "9.463677","sentiment": 0.633517016156253},
     *                          {"city": "Burgos","grade": "8.724951","sentiment": 0.6041372706116136},
     *                          {"city": "Pisa","grade": "9.207483","sentiment": 0.6028689742527156},
     *                          {"city": "Paris","grade": "9.041719","sentiment": 0.5961088823466875}
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
        return response()->json(compact('data'), JsonResponse::HTTP_OK);
    }

    /**
     * @OA\GET(
     *      path="/api/comments/cities",
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
        if (is_null($data)){
            return null;
        } else {
            return response()->json($data, JsonResponse::HTTP_OK);
        }
    }

    /**
     * @OA\POST(
     *      path="/api/comments/data",
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
        if (is_null($data)){
            return null;
        } else {
            return response()->json($data, JsonResponse::HTTP_OK);
        }
    }

    /**
     * @OA\POST(
     *      path="/airports/comments/new-comment",
     *      operationId="insertUserComment",
     *      tags={"comments"},
     *      summary="User insert new comment",
     *      description="Registered user send new comment from the city of the airport.",
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                      @OA\Property(
     *                          type="array",@OA\Items(type="json"),
     *                          description="Comment data"
     *                      ),
     *                      example={
     *                          {"title": "Tryout", "original_message": "This my first comment", "grade": 4.00, "airport_id": 5327},
     *                          {"title": "Comment title", "original_message": "This a new comment", "grade": 5.00, "airport_id": 5327},
     *                    }
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="Ok. No content displayed.",
     *          content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
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
     *                              "The date time field is required.",
     *                              "The airport id field is required.",
     *                              "The grade field is required.",
     *                              "The title field is required.",
     *                              "The original message field is required.",
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
     *      security={
     *         {"bearer": {}}
     *     }
     *  )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function insertUserComment(Request $request){
        $validator = Validator::make($request->json()->all(), [
            'date_time' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
            'airport_id' => ['required', 'integer', 'exists:airports,id'],
            'grade' => ['required', 'integer'],
            'title' => ['required', 'string'],
            'original_message' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return failValidation($validator);
        } else {
            $user = Auth::user();
            $data=array("lib"=>1, "msg"=>$request->get('original_message'));
            $sentimentTranslate = AnalysisController::analyze($data);
            Comment::create([
                'sentiment' => $sentimentTranslate["subjectivity"],
                'polarity' => $sentimentTranslate["polarity"],
                'grade' => $request->get('grade'),
                'title' => $request->get('title'),
                'original_message' => $request->get('original_message'),
                'message' => $sentimentTranslate["message"],
                'date_time' => $request->get('date_time'),
                'city_id' => AirportsController::getCityID($request->get('airport_id')),
                'user_id' => $user->id
            ]);
            return response()->json(null, JsonResponse::HTTP_NO_CONTENT);
        }
    }
}
