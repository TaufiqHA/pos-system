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
        Schema::create('purchases', function (Blueprint $table) {
            $table->string('id')->primary(); // id varchar [pk]
            $table->string('invoice')->unique(); // invoice varchar [unique]
            
            // Definisikan kolom foreign key
            $table->string('supplier_id')->nullable();
            $table->string('branch_id');
            $table->string('user_id');

            // Relasi (pastikan tipe data ID di tabel referensi juga string/varchar jika menggunakan UUID)
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->dateTime('date'); // date datetime
            $table->decimal('subtotal', 15, 2); // subtotal decimal
            $table->decimal('discount', 15, 2)->default(0); // discount decimal
            $table->decimal('tax', 15, 2)->default(0); // tax decimal
            $table->decimal('grand_total', 15, 2); // grand_total decimal
            $table->string('status'); // status varchar
            
            $table->timestamps(); // created_at dan updated_at datetime
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
