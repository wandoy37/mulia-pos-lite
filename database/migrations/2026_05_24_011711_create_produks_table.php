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
        Schema::create('produks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('satuan_id');
            $table->string('nama_produk');
            $table->decimal('harga_beli_terakhir', 15, 2);
            $table->decimal('harga_jual', 15, 2);
            $table->integer('stok_saat_ini');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('satuan_id')->references('id')->on('satuans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
