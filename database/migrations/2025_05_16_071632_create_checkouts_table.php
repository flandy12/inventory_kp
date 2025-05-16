<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('checkouts', function (Blueprint $table) {
            $table->id();

            // User yang melakukan transaksi (kasir)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Total harga semua item yang dibeli
            $table->decimal('total_price', 12, 2);

            // Status pembayaran
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');

            // Metode pembayaran (opsional)
            $table->string('payment_method')->nullable(); // e.g. cash, QRIS, debit

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('checkouts');
    }
};
