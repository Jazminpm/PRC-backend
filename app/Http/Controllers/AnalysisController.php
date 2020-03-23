<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    function analyze(Request $request)
    {
//        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
//            exec('..\vendor\python\venv\Scripts\python ..\vendor\python\src\analysis.py ' . json_encode($request->getContent()), $output_array);
//            $response = json_decode($output_array[0], true);
//            return response($response, 200);
//        } else {
//            exec('../vendor/python/venv/bin/python ../vendor/python/src/analysis.py ' . json_encode($request->getContent()), $output_array);
//            $response = json_decode($output_array[0], true);
//            return response($response, 200);
//        }

//        $cmd = storage_path('../vendor/python/venv/bin/python ../vendor/python/src/analysis.py ' . json_encode($request->getContent()));
        dd(config('app.python'));
//        $ans = shell_exec($cmd);
//        dd($ans);

        $response = json_decode($output_array[0], true);
        return response($response, 200);
    }

    function translate(Request $request)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec('..\vendor\python\venv\Scripts\python ..\vendor\python\src\translate.py ' . json_encode($request->getContent()), $output_array);
            $response = json_decode($output_array[0], true);
            return response($response, 200);
        } else {
            exec('../vendor/python/venv/bin/python ../vendor/python/src/translate.py ' . json_encode($request->getContent()), $output_array);
            $response = json_decode($output_array[0], true);
            return response($response, 200);
        }
    }
}
