<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ModelController extends Controller
{
    function models(Request $request){
        $cmd = config('python.exec');
        $script = config('python.scripts') . 'model_'.$request->id.'.py';
        $result = executePython($cmd, $script, $request);
        return response($result, 200);
    }
}
