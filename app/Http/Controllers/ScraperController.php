<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScraperController extends Controller
{
    function scrapers(Request $request){
        $script = config('python.scripts') . 'scraper_'.$request->id.'.py';
        return response(executePython($script, $request), 200);
    }
}
