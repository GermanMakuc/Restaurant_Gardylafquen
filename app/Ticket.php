<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'state_id', 'tip', 'subtotal', 'total'
    ];
}
