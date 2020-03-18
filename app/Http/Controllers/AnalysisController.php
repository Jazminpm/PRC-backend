<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;


class AnalysisController extends Controller
{
    function store(Request $request)
    {
        $cmd = "vendor/python/venv/bin/python vendor/python/src/analysis/utils.py";
        $args = json_decode($request, true);
        $process = new Process(['../vendor/python/venv/bin/python', '../vendor/python/src/analysis/utils.py', $args]);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $response = json_decode($process->getOutput(), true);

        return response($response, 200);
    }
}
