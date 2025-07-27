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
        Schema::create('purchases', function (Blueprint $table) {
        $table->id();
        $table->string('purchase_number')->unique();
        $table->foreignId('supplier_id')->constrained('suppliers');
        $table->foreignId('user_id')->comment('Creator ID')->constrained('users');
        $table->foreignId('approver_id')->nullable()->comment('Approver ID')->constrained('users');
        $table->date('purchase_date');
        $table->decimal('total_price', 15, 2);
        $table->decimal('down_payment', 15, 2)->default(0);
        $table->decimal('shipping_cost', 15, 2)->default(0);
        $table->decimal('remaining_debt', 15, 2);
        $table->string('payment_status');
        $table->string('approval_status')->default('Pending');
        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
