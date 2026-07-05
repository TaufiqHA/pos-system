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
        Schema::create('product_branch_prices', function (Blueprint $table) {
            $table->string('id')->primary();

            $table->string('product_id');
            $table->string('branch_id');

            $table->decimal('sell_price', 15, 2);

            $table->timestamps();

            // Foreign keys constraints
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            // Unique constraint untuk kombinasi product_id dan branch_id
            $table->unique(['product_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_branch_prices');
    }
};
