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
        Schema::create('sales', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('invoice')->unique();
            $table->string('branch_id')->nullable();
            $table->string('outlet_id')->nullable();
            $table->string('user_id')->nullable();
            $table->dateTime('date');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2);
            $table->string('status');
            $table->uuid('create_by')->nullable();
            $table->timestamps();

            // Relasi Foreign Key
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('outlet_id')->references('id')->on('outlets')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('create_by')->references('id')->on('users')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
