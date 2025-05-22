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
        Schema::create('perjalanan_dinas', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_spt')->unique()->comment('Nomor Surat Perintah Tugas, diisi otomatis');
            $table->date('tanggal_spt');
            $table->enum('jenis_spt', [
                'dalam_daerah', // Dalam Kabupaten/Kota > 8 Jam
                'luar_daerah_dalam_provinsi', // Luar Kabupaten/Kota tapi masih dalam Provinsi
                'luar_daerah_luar_provinsi'   // Luar Kabupaten/Kota dan Luar Provinsi
            ])->comment('Jenis perjalanan dinas berdasarkan cakupan wilayah');
            $table->string('jenis_kegiatan')->default('Biasa')->comment('Misal: Biasa, Diklat, Konsultasi, Survey, Rapat Koordinasi, Mendampingi');
            $table->string('tujuan_spt')->comment('Nama tempat/instansi yang dituju');
            $table->string('provinsi_tujuan_id')->nullable()->comment('Nama provinsi tujuan jika relevan dengan SBU (misal: RIAU, ACEH)');
            $table->string('kota_tujuan_id')->nullable()->comment('Nama kota/kabupaten tujuan jika relevan dengan SBU');
            $table->text('dasar_spt')->comment('Dasar hukum atau surat tugas acuan');
            $table->text('uraian_spt')->comment('Detail maksud dan tujuan perjalanan dinas');
            $table->integer('lama_hari');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', [
                'draft',                    // Disimpan oleh operator, belum diajukan
                'diproses',                 // Diajukan operator, menunggu verifikator
                'revisi_operator_verifikator', // Dikembalikan verifikator ke operator untuk revisi
                'menunggu_persetujuan_atasan',// Diverifikasi, menunggu atasan
                'revisi_operator_atasan',   // Dikembalikan atasan ke operator untuk revisi
                'disetujui',                // Disetujui atasan, siap dilaksanakan
                'selesai',                  // Perjalanan dinas selesai, SPT/SPPD bisa diunduh, laporan bisa diisi
                'ditolak_verifikator',      // Ditolak oleh verifikator
                'ditolak_atasan',           // Ditolak oleh atasan
                'dibatalkan'                // Dibatalkan oleh operator sebelum diproses lanjut
            ])->default('draft');
            $table->text('catatan_verifikator')->nullable();
            $table->text('catatan_atasan')->nullable();

            $table->foreignId('operator_id')->constrained('users')->comment('User (operator) yang membuat pengajuan');
            $table->foreignId('verifikator_id')->nullable()->constrained('users')->comment('User (verifikator) yang memverifikasi');
            $table->foreignId('atasan_id')->nullable()->constrained('users')->comment('User (atasan) yang menyetujui');
            // Tambahkan foreign key lain jika ada proses approval berlapis

            $table->decimal('total_estimasi_biaya', 15, 2)->nullable();
            $table->decimal('total_biaya_rill', 15, 2)->nullable()->comment('Diisi setelah ada laporan realisasi');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perjalanan_dinas');
    }
};