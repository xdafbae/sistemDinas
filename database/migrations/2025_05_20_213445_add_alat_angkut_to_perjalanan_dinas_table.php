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
        Schema::table('perjalanan_dinas', function (Blueprint $table) {
            $table->string('alat_angkut')->nullable()->after('uraian_spt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perjalanan_dinas', function (Blueprint $table) {
            //
        });
    }
};
