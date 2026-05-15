<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Netflix extends Model
{
    protected $table = 'netflixes';
    protected $fillable = [
        'email',
        'password',
        'token2fa',
        'expired_at',
    ];
}
