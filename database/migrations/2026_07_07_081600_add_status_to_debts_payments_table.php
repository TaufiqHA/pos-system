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
        if (Schema::hasTable('debts_payments')) {
            if (! Schema::hasColumn('debts_payments', 'status')) {
                Schema::table('debts_payments', function (Blueprint $table) {
                    $table->string('status')->default('CONFIRMED');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('debts_payments')) {
            if (Schema::hasColumn('debts_payments', 'status')) {
                Schema::table('debts_payments', function (Blueprint $table) {
                    $table->dropColumn('status');
                });
            }
        }
    }
};
