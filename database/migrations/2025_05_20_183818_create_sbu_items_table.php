<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sbu_items', function (Blueprint $table) {
            $table->id();
            $table->string('kategori_biaya'); // Misal: UANG_HARIAN, PENGINAPAN, TRANSPORTASI_DARAT, TRANSPORTASI_UDARA, REPRESENTASI, TRANSPORTASI_LOKAL_KABUPATEN, TRANSPORTASI_KECAMATAN_DESA
            $table->string('uraian_biaya'); // Misal: Luar Kabupaten, Diklat, Pejabat Daerah, Eselon I, Golongan IV, Riau, Aceh, Siak ke Dayun
            $table->string('provinsi_tujuan')->nullable(); // Nama provinsi jika berlaku
            $table->string('kota_tujuan')->nullable(); // Nama kota/kabupaten jika berlaku (bisa lebih spesifik dari provinsi)
            $table->string('kecamatan_tujuan')->nullable(); // Untuk transportasi dalam kabupaten/kecamatan
            $table->string('desa_tujuan')->nullable(); // Untuk transportasi dalam kabupaten/kecamatan
            $table->string('satuan'); // Misal: OH, Tiket, Kali, KM
            $table->decimal('besaran_biaya', 15, 2);
            $table->string('tipe_perjalanan')->nullable(); // Misal: LUAR_DAERAH, DALAM_DAERAH_LUAR_KABUPATEN, DALAM_KABUPATEN_LEBIH_8_JAM, DALAM_KABUPATEN_KURANG_8_JAM, DIKLAT
            $table->string('tingkat_pejabat_atau_golongan')->nullable(); // Misal: KEPALA_DAERAH, ESELON_I, ESELON_II, ESELON_III_GOL_IV, ESELON_IV_GOL_III, GOL_II_I_NON_ASN
            $table->text('keterangan')->nullable();
            $table->integer('jarak_km_min')->nullable(); // Untuk transportasi berdasarkan jarak
            $table->integer('jarak_km_max')->nullable(); // Untuk transportasi berdasarkan jarak
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sbu_items');
    }
};