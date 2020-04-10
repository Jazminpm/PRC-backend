<?php
function executePython($cmd, $file_name, $request) {
    exec($cmd.' '.$file_name.' '.json_encode($request->getContent()), $output_array);
    return $output_array;
}
