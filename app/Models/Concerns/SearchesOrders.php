<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait SearchesOrders
{
    /**
     * Các cột được ô tìm kiếm ở trang tool dò (mã đơn nội bộ, mã đơn đối tác, UID, ...).
     */
    abstract protected function orderSearchColumns(): array;

    /**
     * Tìm đơn theo mã đơn hàng: order_id (mã đơn item) hoặc sales_agent_id (mã đơn bên
     * doitac, vd 45359) hoặc UID. Bỏ khoảng trắng và dấu "#" hay bị dán kèm khi copy mã đơn.
     */
    public function scopeSearchOrder(Builder $query, ?string $term): Builder
    {
        $term = ltrim(trim((string) $term), '#');

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            foreach ($this->orderSearchColumns() as $column) {
                $q->orWhere($column, 'like', "%{$term}%");
            }
        });
    }
}
