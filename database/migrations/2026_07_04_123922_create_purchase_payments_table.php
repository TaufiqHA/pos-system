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
        Schema::create('purchase_payments', function (Blueprint $table) {
            // id varchar [pk]
            $table->string('id')->primary();
            
            // purchase_id varchar [ref: > purchases.id]
            $table->string('purchase_id');
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
            
            // field lainnya
            $table->string('method');
            $table->decimal('amount', 15, 2); // Sesuaikan panjang digit desimal dengan kebutuhan bisnis
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
        Schema::dropIfExists('purchase_payments');
    }
};
