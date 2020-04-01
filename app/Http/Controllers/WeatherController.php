<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeatherController extends Controller
{
    /**
     * INSERT weather data if possible. UPDATE otherwise.
     *
     * @param Request $request json encoded data
     * @return \Illuminate\Contracts\Routing\ResponseFactory code status
     */
    function insert(Request $request)
    {
        $json = json_decode($request->getContent(), true);
        DB::table('weathers')->updateOrInsert([
            'date_time' => $json['date_time'],
            'airport_id' => $json['airport_id']
        ], $json);
        return response()->setStatusCode(200);
    }
}
