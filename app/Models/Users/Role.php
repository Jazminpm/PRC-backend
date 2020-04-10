<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'id', 'type'
    ];

    // disable timestamps created_at & updated_at
    public $timestamps = false;
}
