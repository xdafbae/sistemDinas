<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perjalanan_dinas', function (Blueprint $table) {
            // Ubah menjadi nullable jika belum, dan pastikan unique jika diperlukan setelah diisi
            $table->string('nomor_spt')->nullable()->unique(false)->change(); // Hapus unique sementara jika sudah ada
        });
    }

    public function down(): void
    {
        Schema::table('perjalanan_dinas', function (Blueprint $table) {
            // Kembalikan ke state sebelumnya jika perlu (misal, not nullable)
            // $table->string('nomor_spt')->nullable(false)->unique()->change();
            // Untuk amannya, jika sebelumnya not nullable, jangan drop unique constraint di down()
            // kecuali jika Anda yakin. Cukup ubah nullability.
            $table->string('nomor_spt')->nullable(false)->change();
        });
    }
};