<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WwmOrder extends Model
{
    protected $table = 'wwm_orders';

    protected $fillable = [
        'order_id',
        'sales_agent_id',
        'uid',
        'image',
        'product_id',
        'status',
    ];
}
