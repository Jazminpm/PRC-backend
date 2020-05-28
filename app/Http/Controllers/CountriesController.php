<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CountriesController extends Controller
{
    public static function getCountryName($country_id)
    {
        $url = DB::table('countries')
            ->select('name')
            ->where('id', $country_id)->first();
        if (is_null($url)){
            return null;
        } else {
            return $url->name;
        }
    }

    /**
     * @OA\GET(
     *      path="/api/countries/countries",
     *      operationId="getCountries",
     *      tags={"countries"},
     *      summary="Get all countries",
     *      description="Returns all the countries of the database.",
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
     *                          {"id": 1,"name": "Afghanistan"},
     *                          {"id": 2,"name": "Albania"},
     *                          {"id": 3,"name": "Algeria"},
     *                          {"id": 4,"name": "American Samoa"},
     *                          {"id": 5,"name": "Angola"}
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
    public static function getCountries()
    {
        $data = DB::table('countries')
            ->select(['id', 'name'])->get();
        if (is_null($data)){
            return null;
        } else {
            return response()->json($data, JsonResponse::HTTP_OK);
        }
    }
}
