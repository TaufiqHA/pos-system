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
        Schema::create('sales_payments', function (Blueprint $table) {
            $table->string('id')->primary();

            $table->string('sale_id');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');

            $table->string('method');
            $table->decimal('amount', 15, 2);
            $table->string('status');
            $table->string('reference')->nullable();

            $table->dateTime('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_payments');
    }
};
