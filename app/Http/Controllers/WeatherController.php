<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeatherController extends Controller
{
    function insert(Request $request)
    {
        $json = json_decode($request->getContent(), true);
        DB::table('weathers')->updateOrInsert($json);
        return response()->json($json, 200);
    }
}
