<?php

namespace App\Models\Fligths;

use Illuminate\Database\Eloquent\Model;

class Airline extends Model
{
    protected $fillable = [
        "id", "name", "alias", "iata", "icao", "callsign", "country_id"
    ];

    // disable timestamps created_at & updated_at
    public $timestamps = false;
}
