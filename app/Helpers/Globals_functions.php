<?php
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

function executePython($cmd, $file_name, $request) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        exec($cmd.' '.$file_name.' '.json_encode($request->getContent()), $output_array);
        return $output_array;
    } else {
        // start the process
        $process = new Process([$cmd, $file_name, json_encode($request->json()->all())]);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // get response
        return $process->getOutput();
    }
}
function executePythonNoRequest($cmd, $file_name) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        exec($cmd.' '.$file_name.' ',$output_array);
        return $output_array;
    } else {
        // start the process
        $process = new Process([$cmd, $file_name]);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // get response
        return $process->getOutput();
    }
}
