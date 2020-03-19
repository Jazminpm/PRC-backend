<?php

namespace App\Models\Fligths;

use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    protected $fillable = [
        'id', 'name', 'iata', 'icao', 'latitude', 'longitude', 'country', 'altitude'
    ];

    // disable timestamps created_at & updated_at
    public $timestamps = false;
}
