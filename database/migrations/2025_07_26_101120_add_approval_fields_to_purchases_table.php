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
    Schema::table('purchases', function (Blueprint $table) {
        $table->renameColumn('approver_id', 'approved_by');
        $table->timestamp('approved_at')->nullable()->after('approved_by');
    });
}

public function down(): void
{
    Schema::table('purchases', function (Blueprint $table) {
        $table->renameColumn('approved_by', 'approver_id');
        $table->dropColumn('approved_at');
    });
}
};
