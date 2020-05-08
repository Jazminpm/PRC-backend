<?php

namespace App\Models\Fligths;

use Illuminate\Database\Eloquent\Model;

class FlightStatuses extends Model
{
    protected $fillable = [
        'id', 'name',
    ];

    // disable timestamps created_at & updated_at
    public $timestamps = false;
}
