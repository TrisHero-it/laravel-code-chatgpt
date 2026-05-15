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
        Schema::create('wwm_orders', function (Blueprint $table) {
            $table->id();
            $table->integer("sales_agent_id")->nullable();
            $table->integer("order_id")->nullable();
            $table->string("uid");
            $table->text('image')->nullable();
            $table->string('product_id');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wwm_orders');
    }
};
