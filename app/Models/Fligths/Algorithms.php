<?php

namespace App\Models\Fligths;

use Illuminate\Database\Eloquent\Model;

class Algorithms extends Model
{
    protected $fillable = [
        'id', 'name',
    ];

    // disable timestamps created_at & updated_at
    public $timestamps = false;
}
