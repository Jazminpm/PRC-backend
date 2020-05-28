<?php

use Illuminate\Http\JsonResponse;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

function executePython($script, $request) {
    $cmd = config('python.exec');

    // Request en formato correcto
    if (gettype($request) != "array"){
        $args = $request->json()->all();
    } else {
        $args = $request;

    }
    $args = json_encode($args, True);

    // dd($cmd.' '.$script." '".$args."'");
    // Iniciamos el proceso
    $process = new Process([$cmd, $script, $args]);
    $process->setTimeout(100000);
    $process->run();

    // executes after the command finishes
    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }

    $response =  $process->getOutput(); // Python result is in one print
    $response = array_filter( explode("\n", $response));

    return $response;
}

function failValidation($validator){
    $response = [];
    foreach ($validator->errors()->getMessages() as $item) {
        array_push($response, $item);
    }
    return response()->json(["errors" => $response], JsonResponse::HTTP_BAD_REQUEST);
}
