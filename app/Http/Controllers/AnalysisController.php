<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AnalysisController extends Controller
{
    function analyze(Request $request)
    {
        // get requested parameters & set scripts
        $cmd = config('python.exec');
        $script = config('python.scripts') . 'analysis.py';
        $json = $request->json()->all();

        // start the process
        $process = new Process([$cmd, $script, json_encode($json)]);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // get response
        $response = json_decode($process->getOutput(), true);
        return response($response, 200);
    }

    function translate(Request $request)
    {
        // get requested parameters & set scripts
        $cmd = config('python.exec');
        $script = config('python.scripts') . 'translate.py';
        $json = $request->json()->all();

        // start the process
        $process = new Process([$cmd, $script, json_encode($json)]);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // get response
        $response = json_decode($process->getOutput(), true);
        return response($response, 200);
    }
}
