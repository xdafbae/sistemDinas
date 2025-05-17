<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration // Untuk Laravel 9+
// class AddNomorHpToUsersTable extends Migration // Untuk Laravel < 9
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nomor_hp')->nullable()->unique()->after('jabatan'); // Tambahkan setelah kolom 'jabatan' (opsional penempatannya)
            // unique() bersifat opsional, tergantung apakah nomor HP harus unik atau tidak
            // nullable() berarti kolom ini boleh kosong
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nomor_hp');
        });
    }
};