<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('midasbuy_tokens', function (Blueprint $table) {
            $table->id();
            $table->string("order_id");
            $table->string("token");
            $table->string("uid");
            $table->string("status")->default("pending");
            $table->string("code");
            $table->string("image")->nullable();
            $table->string("sale_agent_id")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('midasbuy_tokens');
    }
};
