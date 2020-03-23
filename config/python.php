<?php

function getPath()
{
    $path = storage_path('app' . DIRECTORY_SEPARATOR . 'python' . DIRECTORY_SEPARATOR . 'venv' . DIRECTORY_SEPARATOR);

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $python_path = $path . 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe';
    } else {
        $python_path = $path . 'bin' . DIRECTORY_SEPARATOR . 'python';
    }
    return $python_path;
}

return [

    /*
    |--------------------------------------------------------------------------
    | Python exec path
    |--------------------------------------------------------------------------
    |
    | This value is the path of the python environment. This value is used when
    | the framework needs to execute a python script
    |
    */

    'exec' => getPath(),


    /*
    |--------------------------------------------------------------------------
    | Python scripts path
    |--------------------------------------------------------------------------
    |
    | This value is the path of the python scripts folder. This value is used when
    | the framework needs to use a python script
    |
    */

    'scripts' => storage_path('app' . DIRECTORY_SEPARATOR . 'python' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR),
];
