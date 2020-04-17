<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ModelController extends Controller
{
    function models(Request $request){
        $script = config('python.scripts') . 'model_'.$request->id.'.py';
        return response(executePython($script, $request), 200);
    }
}
