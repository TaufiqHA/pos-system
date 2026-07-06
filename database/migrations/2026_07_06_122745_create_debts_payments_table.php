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
        Schema::create('debts_payments', function (Blueprint $table) {
            $table->string('id')->primary();

            $table->string('debt_id');
            $table->dateTime('payment_date');
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('method');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->string('created_by')->nullable();

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('debt_id')->references('id')->on('debts')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debts_payments');
    }
};
