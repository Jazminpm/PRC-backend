<?php

namespace App\Models\Models;

use Illuminate\Database\Eloquent\Model;

class InUse extends Model
{
    protected $fillable = [
        'model', 'analysis',
    ];

    // disable timestamps created_at & updated_at
    public $timestamps = false;
}
