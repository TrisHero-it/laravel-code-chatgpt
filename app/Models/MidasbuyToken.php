<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MidasbuyToken extends Model
{
    /** Đơn chưa nạp, tool sẽ lấy đơn này */
    public const STATUS_PENDING = 'pending';
    /** Đơn bị hoãn vì thiếu code token, tới delayed_until thì tự về pending */
    public const STATUS_DELAYED = 'delayed';

    protected $fillable = [
        'order_id',
        'token',
        'uid',
        'status',
        'code',
        'image',
        'sale_agent_id',
        'delayed_until',
        'delay_reason',
    ];

    protected $casts = [
        'delayed_until' => 'datetime',
    ];

    /**
     * Chốt chặn cuối: mọi đơn phải có sale_agent_id (id đơn bên đối tác).
     *
     * Đặt ở model vì ImportDoitacOrders gọi MidasbuyToken::create() trực tiếp,
     * không đi qua FormRequest. Thiếu field này thì tool nạp xong sẽ gọi
     * .../api/orders//processed -> không bao giờ trả 200 -> retry 6 lần x 10
     * phút rồi lặp lại mãi, chặn toàn bộ hàng đợi.
     */
    protected static function booted(): void
    {
        static::creating(function (self $don) {
            if ($don->sale_agent_id === null || $don->sale_agent_id === '') {
                throw new \InvalidArgumentException(
                    'Không thể tạo đơn MidasBuy Token khi thiếu sale_agent_id (id đơn đối tác).'
                );
            }
        });
    }

    /**
     * Hoãn đơn lại $minutes phút, tool sẽ bỏ qua đơn này để làm đơn khác trước.
     */
    public function delay(int $minutes, ?string $reason = null): void
    {
        $this->status = self::STATUS_DELAYED;
        $this->delayed_until = now()->addMinutes($minutes);
        $this->delay_reason = $reason;
        $this->save();
    }

    /**
     * Hoãn TẤT CẢ đơn đang chờ của một mức token, dùng chung một mốc
     * delayed_until. Khi kho hết code mức đó thì mọi đơn cùng mức đều vô ích,
     * nên hoãn cả cụm để tool chuyển sang mức token khác.
     *
     * Dùng chung một mốc nên khi hết hạn cả cụm quay lại cùng lúc, và vì đơn
     * được lấy theo thứ tự id tăng dần, đơn cũ nhất của mức đó luôn chạy trước.
     *
     * @return int Số đơn đã hoãn
     */
    public static function delayAllByToken(string $token, int $minutes, ?string $reason = null): int
    {
        return static::where('status', self::STATUS_PENDING)
            ->where('token', $token)
            ->update([
                'status' => self::STATUS_DELAYED,
                'delayed_until' => now()->addMinutes($minutes),
                'delay_reason' => $reason,
            ]);
    }

    /**
     * Trả các đơn đã hết thời gian hoãn về pending. Được gọi trước khi lấy đơn
     * mới nên không cần cron riêng.
     */
    public static function releaseExpiredDelays(): int
    {
        return static::where('status', self::STATUS_DELAYED)
            ->whereNotNull('delayed_until')
            ->where('delayed_until', '<=', now())
            ->update([
                'status' => self::STATUS_PENDING,
                'delayed_until' => null,
                'delay_reason' => null,
            ]);
    }
}
