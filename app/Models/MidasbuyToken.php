<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MidasbuyToken extends Model
{
    protected $fillable = [
        'order_id',
        'token',
        'uid',
        'status',
        'code',
        'image',
        'sale_agent_id',
    ];
}
