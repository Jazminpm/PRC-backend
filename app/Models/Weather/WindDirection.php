<?php

namespace App\Models\Weather;

use Illuminate\Database\Eloquent\Model;

class WindDirection extends Model
{
    protected $fillable = [
        'direction'
    ];

    // disable timestamps created_at & updated_at
    public $timestamps = false;
}
