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
            'sentiment' => $json['sentiment'],
            'polarity' => $json['polarity'],
            'grade' => $json['grade'],
            'original_message' => $json['original_message'],
            'message' => $json['message'],
            'library' => '1',  # textblob
            'date' => $json['date']
        ], $json);
    }
}
