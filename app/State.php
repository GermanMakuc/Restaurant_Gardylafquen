<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = [
        'status', 'table', 'user_id', 'table', 'notes'
    ];
}
