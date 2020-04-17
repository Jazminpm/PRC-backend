<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AnalysisController extends Controller
{
    function analyze(Request $request) {
        // get requested parameters & set scripts
        $script = config('python.scripts') . 'analysis.py';
        $json = $request->json()->all();
        $args = '{"lib": ' . $json['lib'] . ' ,"msg": "' . $json['msg'] . '"}';
        return response(executePython($script, $args), 200);
        // return response($response, 200);
    }

    function translate(Request $request){
        // get requested parameters & set scripts
        $cmd = config('python.exec');
        $script = config('python.scripts') . 'translate.py';
        $response = json_decode(executePython($cmd, $script, $request)[0], true);
        return response($response, 200);
    }
}
