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
        Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('product_code')->unique();
        $table->string('product_name');
        $table->text('description')->nullable();
        $table->string('product_type');
        $table->string('unit');
        $table->integer('current_stock')->default(0);
        $table->integer('minimum_stock')->default(0);
        $table->decimal('last_buy_price', 15, 2)->nullable();
        $table->decimal('sell_price', 15, 2);
        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
