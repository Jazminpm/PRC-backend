<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    public static function analyze($request) {
        // get requested parameters & set scripts
        $script = config('python.scripts') . 'analysis.py';
        return json_decode(executePython($script, $request)[0], true);
    }

    public static function translate($request){
        // get requested parameters & set scripts
        $script = config('python.scripts') . 'translate.py';
        return response(executePython($script, $request), 200);
    }
}
