<?php
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

function executePython($script, $request) {
    $cmd = config('python.exec');

    // Request en formato correcto
    $json = $request->json()->all();
    $args = "{";
    foreach ($json as $key => $part) {
        if (gettype($part) === 'string')
            $args = $args.'"'.$key.'":'.'"'.$part.'",';
        else
            $args = $args.'"'.$key.'":'.''.$part.',';
    }
    $args = substr($args, 0, -1)."}";

    // Iniciamos el proceso
    $process = new Process([$cmd, $script, $args]);
    $process->run();

    // executes after the command finishes
    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }

    $response = json_decode($process->getOutput(), true);

    // For see all de print results (we dont need it with database)
    foreach ($process as $type => $data) {
        $response = $data;
    }

    return response($response, 200);
}
