<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    function analyze(Request $request) {
        // get requested parameters & set scripts
        $script = config('python.scripts') . 'analysis.py';
        return response(executePython($script, $request), 200);
    }

    function translate(Request $request){
        // get requested parameters & set scripts
        $script = config('python.scripts') . 'translate.py';
        return response(executePython($script, $request), 200);
    }
}
