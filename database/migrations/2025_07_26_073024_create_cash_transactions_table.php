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
       Schema::create('cash_transactions', function (Blueprint $table) {
        $table->id();
        $table->date('transaction_date');
        $table->foreignId('account_id')->constrained('accounts');
        $table->string('type'); // In, Out
        $table->decimal('amount', 15, 2);
        $table->string('description');
        $table->unsignedBigInteger('related_transaction_id')->nullable();
        $table->foreignId('user_id')->constrained('users');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
