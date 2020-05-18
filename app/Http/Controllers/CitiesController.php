<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CitiesController extends Controller
{
    public static function getCityName($city_id)
    {
        $name = DB::table('cities')
            ->select('name')
            ->where('id', $city_id)->first();
        if (is_null($name)){
            return null;
        } else {
            return $name->name;
        }
    }

    /**
     * @OA\GET(
     *      path="/api/cities/cities",
     *      operationId="getCities",
     *      tags={"cities"},
     *      summary="Get all cities",
     *      description="Returns all the cities  of the database.",
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
     *                          {"id": 1,"name": "Bayman"},
     *                          {"id": 2,"name": "Camp Bastion"},
     *                          {"id": 30,"name": "Annaba"},
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
    public static function getCities()
    {
        $data = DB::table('cities')
            ->select(['id', 'name'])->get();
        if (is_null($data)){
            return null;
        } else {
            return response()->json($data, JsonResponse::HTTP_OK);
        }
    }
}

