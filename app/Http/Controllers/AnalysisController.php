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
        $script = config('python.scripts') . 'analysis.py';
        $json = $request->json()->all();

        $process = new Process([$cmd, $script, '{"lib": ' . $json['lib'] . ' ,"msg": "' . $json['msg'] . '"}']);
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
        // get request content
        $json = $request->json()->all();

        // make the command
        $cmd = config('python.exec') . ' ' . config('python.scripts') . 'translate.py ' . $json;
        exec($cmd, $output_array);

        // get response
        $response = json_decode($output_array[0], true);
        return response($response, 200);
    }
}
