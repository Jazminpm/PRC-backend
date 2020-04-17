<?php

use Symfony\Component\Process\Process;


function executePython($script, $args) {
    $cmd = config('python.exec');
    $process = new Process([$cmd, $script, $args]);
    $process->run();

    // executes after the command finishes
    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }

    $response = json_decode($process->getOutput(), true);

    return response($response, 200);
}
