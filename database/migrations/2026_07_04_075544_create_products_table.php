<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('category_id');

            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            $table->string('unit')->nullable();

            $table->decimal('buy_price', 15, 2)->default(0);
            $table->decimal('sell_price', 15, 2)->default(0);

            $table->boolean('is_wholesale')->default(false);

            $table->string('image')->nullable();

            $table->timestamps(); // membuat created_at dan updated_at
            $table->softDeletes(); // membuat deleted_at

            // Relasi ke categories
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

