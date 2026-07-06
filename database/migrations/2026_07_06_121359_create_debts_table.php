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
        Schema::create('debts', function (Blueprint $table) {
            $table->string('id')->primary();

            $table->string('debtor_type'); // branch, outlet
            $table->string('debtor_branch_id')->nullable();
            $table->string('debtor_outlet_id')->nullable();

            $table->string('creditor_type'); // supplier, branch
            $table->string('supplier_id')->nullable();
            $table->string('creditor_branch_id')->nullable();

            $table->string('source_type')->nullable(); // purchase, sale
            $table->string('purchase_id')->nullable();
            $table->string('sale_id')->nullable();

            $table->string('invoice_number')->nullable();

            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);

            $table->dateTime('due_date')->nullable();

            $table->string('status')->default('unpaid'); // unpaid, partial, paid, overdue
            $table->text('notes')->nullable();

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('debtor_branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('debtor_outlet_id')->references('id')->on('outlets')->onDelete('set null');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            $table->foreign('creditor_branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('set null');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
