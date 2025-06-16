<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_perjalanan_dinas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perjalanan_dinas_id')->constrained('perjalanan_dinas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->comment('User (pegawai pelaksana) yang membuat laporan ini');
            $table->date('tanggal_laporan');
            $table->text('ringkasan_hasil_kegiatan');
            $table->text('kendala_dihadapi')->nullable();
            $table->text('saran_tindak_lanjut')->nullable();
            $table->enum('status_laporan', [
                'draft',                        // Disimpan oleh pegawai, belum diserahkan
                'diserahkan_untuk_verifikasi',  // Diserahkan pegawai, menunggu verifikasi Bendahara/Verifikator Laporan
                'revisi_laporan',               // Dikembalikan Bendahara/Verifikator Laporan ke pegawai
                'disetujui_bendahara',          // Disetujui Bendahara, mungkin ada proses lanjut
                'selesai_diproses'              // Semua proses laporan selesai
            ])->default('draft');
            $table->text('catatan_pereview')->nullable()->comment('Catatan dari Bendahara/Verifikator Laporan jika ada revisi/info');
            $table->decimal('total_biaya_rill_dilaporkan', 15, 2)->nullable();
            $table->timestamps();

            // Pastikan satu perjalanan dinas oleh satu user hanya punya satu laporan utama (jika logikanya demikian)
            // Jika per personil bisa ada laporan terpisah meskipun dalam satu SPT, maka constraint ini mungkin tidak perlu
            // atau perlu dimodifikasi. Untuk kasus ini, kita asumsikan satu laporan per user per perjalanan.
            $table->unique(['perjalanan_dinas_id', 'user_id'], 'lpd_perjadin_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_perjalanan_dinas');
    }
};