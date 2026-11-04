<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hòm chuyển tiếp (mail.tm) nhận thư Netflix - ưu tiên 3 khi tra mã,
     * sau tài khoản trong bảng netflixes và hòm thư tổng inbound-mail.
     */
    public function up(): void
    {
        Schema::create('forward_mailboxes', function (Blueprint $table) {
            $table->id();
            $table->string('address')->unique();
            $table->string('password');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forward_mailboxes');
    }
};
