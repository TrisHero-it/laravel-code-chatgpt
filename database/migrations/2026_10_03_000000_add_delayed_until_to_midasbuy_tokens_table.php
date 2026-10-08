<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Đơn thiếu code token sẽ được chuyển sang status "delayed" kèm mốc
     * delayed_until. Tới mốc đó đơn tự quay lại "pending" để tool nạp tiếp.
     */
    public function up(): void
    {
        Schema::table('midasbuy_tokens', function (Blueprint $table) {
            $table->timestamp('delayed_until')->nullable()->after('status');
            $table->string('delay_reason')->nullable()->after('delayed_until');
        });
    }

    public function down(): void
    {
        Schema::table('midasbuy_tokens', function (Blueprint $table) {
            $table->dropColumn(['delayed_until', 'delay_reason']);
        });
    }
};
