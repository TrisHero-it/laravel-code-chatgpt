<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Hòm chuyển tiếp (mail.tm) nhận thư Netflix. CodeController đăng nhập bằng
 * address + password để lấy token (cache theo tokenCacheKey()).
 */
class ForwardMailbox extends Model
{
    protected $fillable = [
        'address',
        'password',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /** Key cache token mail.tm của hòm - xoá khi đổi mật khẩu / xoá hòm. */
    public static function tokenCacheKey(string $address): string
    {
        return 'netflix:root-mailbox-token:' . mb_strtolower(trim($address), 'UTF-8');
    }
}
