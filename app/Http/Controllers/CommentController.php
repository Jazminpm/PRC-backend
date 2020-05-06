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
            'sentiment' => $json['subjectivity'],
            'polarity' => $json['polarity'],
            'grade' => $json['rating'],
            'original_message' => $json['text'],
            'message' => $json['trans_text'],
            'library' => '1',  # textblob
            'date' => $json['date']
        ], $json);
    }
}
