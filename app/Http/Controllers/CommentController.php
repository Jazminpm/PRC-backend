<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    /**
     * INSERT comments data if possible. UPDATE otherwise.
     *
     * @param $json
     */
    public static function insert($json)
    {
        # column => value
        DB::table('comments')->updateOrInsert([
            'original_message' => $json['original_message'],
            'date_time' => $json['date_time']
        ], $json);
    }
}
