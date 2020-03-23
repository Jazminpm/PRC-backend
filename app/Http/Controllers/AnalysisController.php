<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AnalysisController extends Controller
{
    function analyze(Request $request)
    {

        $cmd = config('python.exec');
        $cript = config('python.scripts') . 'analysis.py';
//        dd($cmd);

        $process = new Process([$cmd, $cript, $request->getContent()]);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $response = json_decode($process->getOutput(), true);

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
