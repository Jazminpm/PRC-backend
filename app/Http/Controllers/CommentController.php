<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        DB::table('comments')->updateOrInsert([], $json); //buscar update or insert con key autoincrement
    }
}
