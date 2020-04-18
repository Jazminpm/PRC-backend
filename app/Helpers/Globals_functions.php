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
    if($args == '}'){
        $args = "";
    }

    // dd($cmd.' '.$script.' '.$args);
    // Iniciamos el proceso
    $process = new Process([$cmd, $script, $args]);
    $process->run();

    // executes after the command finishes
    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }

    // executes after the command finishes
    $response = json_decode($process->getOutput(), true);

    return response($response, 200);
}
