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
        Schema::table('cash_transactions', function (Blueprint $table) {
            // Hapus kolom lama
            $table->dropColumn('type');
            $table->dropColumn('amount');

            // Tambahkan kolom baru
            $table->decimal('debit', 15, 2)->default(0)->after('account_id');
            $table->decimal('credit', 15, 2)->default(0)->after('debit');
            $table->decimal('balance', 15, 2)->default(0)->after('credit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            // Kembalikan seperti semula jika di-rollback
            $table->string('type');
            $table->decimal('amount', 15, 2);

            $table->dropColumn(['debit', 'credit', 'balance']);
        });
    }
};
