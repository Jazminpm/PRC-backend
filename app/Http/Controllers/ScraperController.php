<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScraperController extends Controller
{
    /*function historicalWeather (Request $request) {
        // get requested parameters & set scripts
        $cmd = config('python.exec');
        $script = config('python.scripts') . 'scraper_2.py';
        $result = executePython($cmd, $script, $request);

        return response($result, 200);
    }

    function futureWeather () {
        // get requested parameters & set scripts
        $cmd = config('python.exec');
        $script = config('python.scripts') . 'scraper_.py';
        $result = executePythonNoRequest($cmd, $script);

        return response($result, 200);
    }

    function historicalAirportia (Request $request) {
        // get requested parameters & set scripts
        $cmd = config('python.exec');
        $script = config('python.scripts') . 'scraper_3.py';
        $result = executePython($cmd, $script, $request);

        return response($result, 200);
    }*/

    function scrapers(Request $request){
        $cmd = config('python.exec');
        $script = config('python.scripts') . 'scraper_'.$request->id.'.py';
        $result = executePython($cmd, $script, $request);
        return response($result, 200);
    }
}
