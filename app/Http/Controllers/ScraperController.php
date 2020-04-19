<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScraperController extends Controller
{
    function scrapers(Request $request){
        $id = $request->id;
        $script = config('python.scripts') . 'scraper_'.$id.'.py';
        foreach (executePython($script, $request) as $result){
            $data = json_decode($result, true);

            if ($id == 1 or $id == 2){
                WeatherController::insert($data);
            }
        }

        return response('Execute complete', 200);
    }
}
