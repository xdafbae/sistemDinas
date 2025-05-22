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
        // Nama tabel pivot, bisa 'perjalanan_dinas_user' atau 'perjalanan_dinas_personil'
        Schema::create('perjalanan_dinas_personil', function (Blueprint $table) {
            $table->id(); // Opsional, tapi seringkali berguna

            // Foreign key ke tabel perjalanan_dinas
            $table->foreignId('perjalanan_dinas_id')
                  ->constrained('perjalanan_dinas') // Nama tabel utama perjalanan dinas
                  ->onUpdate('cascade')
                  ->onDelete('cascade'); // Jika perjalanan dinas dihapus, entri di pivot juga dihapus

            // Foreign key ke tabel users (untuk personil)
            $table->foreignId('user_id')
                  ->constrained('users') // Nama tabel users
                  ->onUpdate('cascade')
                  ->onDelete('cascade'); // Jika user dihapus, entri di pivot juga dihapus

            // Anda bisa menambahkan kolom lain ke tabel pivot jika perlu
            // Misalnya, peran spesifik personil dalam perjalanan dinas tersebut, dll.
            // $table->string('peran_dalam_perjalanan')->nullable();

            $table->timestamps(); // Opsional, jika Anda ingin tahu kapan relasi dibuat/diupdate

            // Tambahkan unique constraint untuk mencegah duplikasi pasangan yang sama
            $table->unique(['perjalanan_dinas_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perjalanan_dinas_personil');
    }
};