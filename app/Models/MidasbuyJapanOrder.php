<?php

namespace App\Models;

use App\Models\Concerns\SearchesOrders;
use Illuminate\Database\Eloquent\Model;

class MidasbuyJapanOrder extends Model
{
    use SearchesOrders;

    protected $table = 'midasbuy_japan_orders';

    protected $fillable = [
        'order_id',
        'uid',
        'card',
        'sales_agent_id',
        'image',
        'status'
    ];

    protected function orderSearchColumns(): array
    {
        return ['order_id', 'sales_agent_id', 'uid'];
    }
}
