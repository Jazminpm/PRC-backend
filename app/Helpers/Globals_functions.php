<?php

use Illuminate\Http\JsonResponse;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

function executePython($script, $request) {
    $cmd = config('python.exec');

    // Request en formato correcto
    if (gettype($request) != "array"){
        $json = $request->json()->all();
    } else {
        $json = $request;
    }

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
    // executes after command finish
    foreach ($process as $type => $data) {
        $response = array_filter( explode("\n", $data));
    }

    return $response;
}

function failValidation($validator){
    $response = [];
    foreach ($validator->errors()->getMessages() as $item) {
        array_push($response, $item);
    }
    return response()->json(["errors" => $response], JsonResponse::HTTP_BAD_REQUEST);

}
