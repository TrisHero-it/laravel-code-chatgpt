<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MidasbuyJapanOrder extends Model
{
    protected $table = 'midasbuy_japan_orders';

    protected $fillable = [
        'order_id',
        'uid',
        'card',
        'sales_agent_id',
        'image',
        'status'
    ];
}
