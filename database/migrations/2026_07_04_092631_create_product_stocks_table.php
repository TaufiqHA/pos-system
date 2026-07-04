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
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->string('id')->primary(); // Tipe string/varchar untuk Primary Key
            
            $table->string('product_id');
            $table->string('branch_id');
            
            $table->integer('stock')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->decimal('average_cost', 15, 2)->default(0);
            
            $table->timestamps();

            // Foreign keys constraints
            // (Pastikan tabel products dan branches sudah ada dan tipe ID mereka cocok, yaitu string)
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            // Menambahkan constraint Unique untuk kombinasi product_id dan branch_id
            $table->unique(['product_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
    }
};
