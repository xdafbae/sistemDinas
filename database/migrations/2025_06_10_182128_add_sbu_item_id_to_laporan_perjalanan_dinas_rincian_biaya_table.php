<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('laporan_perjalanan_dinas_rincian_biaya', function (Blueprint $table) {
            $table->foreignId('sbu_item_id')->nullable()->after('laporan_perjalanan_dinas_id')->constrained('sbu_items')->onDelete('set null');
        });
    }
    public function down(): void {
        Schema::table('laporan_perjalanan_dinas_rincian_biaya', function (Blueprint $table) {
            $table->dropForeign(['sbu_item_id']);
            $table->dropColumn('sbu_item_id');
        });
    }
};