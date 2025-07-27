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
       Schema::create('returns', function (Blueprint $table) {
        $table->id();
        $table->string('return_number')->unique();
        $table->string('return_type'); // Sale, Purchase
        $table->unsignedBigInteger('related_transaction_id');
        $table->date('return_date');
        $table->text('reason')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_models');
    }
};
