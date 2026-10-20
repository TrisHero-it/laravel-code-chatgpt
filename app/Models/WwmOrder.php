<?php

namespace App\Models;

use App\Models\Concerns\SearchesOrders;
use Illuminate\Database\Eloquent\Model;

class WwmOrder extends Model
{
    use SearchesOrders;

    protected $table = 'wwm_orders';

    protected $fillable = [
        'order_id',
        'sales_agent_id',
        'uid',
        'image',
        'product_id',
        'status',
        'category',
        'server',
    ];

    protected function orderSearchColumns(): array
    {
        return ['order_id', 'sales_agent_id', 'uid'];
    }
}
