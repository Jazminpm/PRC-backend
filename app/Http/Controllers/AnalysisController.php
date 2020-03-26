<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    function analyze(Request $request)
    {
        // get request content
        $json = $request->json()->all();

        // make the command
        $cmd = config('python.exec') . ' ' . config('python.scripts') . 'analysis.py ' . json_encode($json);
        exec($cmd, $output_array);

        // get response
        $response = json_decode($output_array[0], true);
        return response($response, 200);

    }

    function translate(Request $request)
    {
        // get request content
        $json = $request->json()->all();

        // make the command
        $cmd = config('python.exec') . ' ' . config('python.scripts') . 'translate.py ' . json_encode($json);
        exec($cmd, $output_array);

        // get response
        $response = json_decode($output_array[0], true);
        return response($response, 200);
    }
}
