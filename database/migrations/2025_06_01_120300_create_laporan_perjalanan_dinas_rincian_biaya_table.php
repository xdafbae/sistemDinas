<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_perjalanan_dinas_rincian_biaya', function (Blueprint $table) {
            $table->id();

            // Foreign key ke tabel laporan_perjalanan_dinas
            $table->foreignId('laporan_perjalanan_dinas_id')
                  ->constrained('laporan_perjalanan_dinas')
                  ->onDelete('cascade')
                  ->onUpdate('cascade') // Tambahkan onUpdate jika perlu
                  ->name('fk_lpd_rincian_to_lpd'); // <-- NAMA CONSTRAINT KUSTOM YANG LEBIH PENDEK

            // Opsional, untuk referensi ke estimasi atau SBU jika perlu
            // $table->foreignId('perjalanan_dinas_biaya_id')->nullable()->constrained('perjalanan_dinas_biaya')->onDelete('set null')->name('fk_lpd_rincian_to_perjadin_biaya');
            // $table->foreignId('sbu_item_id')->nullable()->constrained('sbu_items')->onDelete('set null')->name('fk_lpd_rincian_to_sbu');

            $table->string('deskripsi_biaya_rill');
            $table->integer('jumlah_rill')->default(1);
            $table->string('satuan_rill', 50);
            $table->decimal('harga_satuan_rill', 15, 2);
            $table->decimal('subtotal_biaya_rill', 15, 2);
            $table->string('nomor_bukti')->nullable();
            $table->string('path_bukti_file')->nullable();
            $table->text('keterangan_rill')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('laporan_perjalanan_dinas_rincian_biaya', function (Blueprint $table) {
            // Saat drop foreign key, gunakan nama constraint yang sama
            $table->dropForeign('fk_lpd_rincian_to_lpd'); // <-- GUNAKAN NAMA KUSTOM SAAT DROP
        });
        Schema::dropIfExists('laporan_perjalanan_dinas_rincian_biaya');
    }
};