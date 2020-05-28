<?php

namespace App\Models\Comments;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //
    protected $fillable = [
        'sentiment', 'polarity', 'grade', 'title', 'place', 'original_message', 'message', 'library', 'date_time', 'city_id', 'user_id'
    ];
    // disable timestamps created_at & updated_at
    public $timestamps = false;
}
