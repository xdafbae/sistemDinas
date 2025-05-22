<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perjalanan_dinas_biaya', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perjalanan_dinas_id')->constrained('perjalanan_dinas')->onDelete('cascade');
            $table->foreignId('sbu_item_id')->nullable()->constrained('sbu_items')->onDelete('set null'); // Referensi ke SBU item, bisa null jika SBU berubah atau dihapus
            $table->foreignId('user_id_terkait')->nullable()->constrained('users')->onDelete('set null'); // Jika biaya ini spesifik untuk satu user dalam perjalanan
            $table->string('deskripsi_biaya'); // Misal "Uang Harian Gol IV Luar Kota - Jakarta"
            $table->integer('jumlah_personil_terkait')->default(1);
            $table->integer('jumlah_hari_terkait')->default(1);
            $table->integer('jumlah_unit')->default(1); // Bisa untuk tiket (1), malam penginapan (N malam)
            $table->decimal('harga_satuan', 15, 2); // Harga per satuan dari SBU
            $table->decimal('subtotal_biaya', 15, 2);
            $table->text('keterangan_tambahan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perjalanan_dinas_biaya');
    }
};