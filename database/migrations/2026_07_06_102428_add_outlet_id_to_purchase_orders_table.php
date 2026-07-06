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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('branch_id')->nullable()->change();
        });

        if (! Schema::hasColumn('purchase_orders', 'outlet_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->string('outlet_id')->nullable()->after('branch_id');
                $table->foreign('outlet_id')->references('id')->on('outlets')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('purchase_orders', 'outlet_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropForeign(['outlet_id']);
                $table->dropColumn('outlet_id');
            });
        }

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('branch_id')->nullable(false)->change();
        });
    }
};
