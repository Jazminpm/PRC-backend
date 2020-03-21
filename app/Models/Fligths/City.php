<?php

namespace App\Models\Fligths;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'id', 'name', 'country_id'
    ];

    // disable timestamps created_at & updated_at
    public $timestamps = false;
}
