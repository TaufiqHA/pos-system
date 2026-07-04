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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->string('id')->primary();

            $table->string('sale_id');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');

            $table->string('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');

            $table->string('sku');
            $table->string('product_name');
            $table->string('unit');

            $table->integer('qty');

            $table->decimal('price', 15, 2);
            $table->decimal('cost', 15, 2);
            $table->decimal('subtotal', 15, 2);

            $table->boolean('is_wholesale')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
