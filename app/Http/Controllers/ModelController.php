<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ModelController extends Controller
{
    function trainModel(Request $request){
        $args = FlightsController::getModelData($request->characteristic, $request->init_date, $request->final_date);

        if (sizeof($args) > 0) {
            $data = ModelController::getData($args, $request->characteristic);
            $args = $request->all();
            array_push($args, $data);

            $script = config('python.scripts') . 'model_1.py';


            $data = json_decode(executePython($script, $args)[0], true);
            ModelController::insertTrain($data);
            return 'Model create';
        } else {
            return ('errors = There are not flights.');
        }
    }

    function getData($args, $characteristics){
        $data_structure = "{";
        foreach ($characteristics as $characteristic) {
            $data_structure .= '"'.$characteristic.'":[],';
        }
        $data_structure = substr($data_structure, 0, -1)."}";
        $data  = json_decode($data_structure, true);

        foreach ($args as $arg) {
            foreach ($characteristics as $characteristic) {
                array_push($data[$characteristic], $arg[$characteristic]);
            }
        }
        return $data;
    }

    public static function insertTrain($data)
    {
        // Get foreign key of airline and a city
        DB::table('models')->updateOrInsert($data);
    }

}
