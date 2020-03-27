<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ScraperController extends Controller
{
    function historicalWeather (Request $request) {
        // get requested parameters & set scripts
        $cmd = config('python.exec');
        $script = config('python.scripts') . 'scraper_tu_tiempo.py';
        $result = executePython($cmd, $script, $request);

        return response($result, 200);
    }

    function futureWeather () {
        // get requested parameters & set scripts
        $cmd = config('python.exec');
        $script = config('python.scripts') . 'scraper_el_tiempo.py';
        $result = executePythonNoRequest($cmd, $script);

        return response($result, 200);
    }

    function historicalAirportia (Request $request) {
        // get requested parameters & set scripts
        $cmd = config('python.exec');
        $script = config('python.scripts') . 'scraper_historical_airportia.py';
        $result = executePython($cmd, $script, $request);

        return response($result, 200);
    }
}
