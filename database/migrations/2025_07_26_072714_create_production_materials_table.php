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
       Schema::create('production_materials', function (Blueprint $table) {
        $table->id();
        $table->foreignId('production_id')->constrained('productions')->onDelete('cascade');
        $table->foreignId('raw_material_id')->constrained('products');
        $table->integer('quantity_used');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_materials');
    }
};
