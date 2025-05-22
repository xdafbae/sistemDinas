<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerjalananDinasBiaya extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'perjalanan_dinas_biaya';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'perjalanan_dinas_id',
        'sbu_item_id',
        'user_id_terkait',       // Opsional: ID user jika biaya ini spesifik untuk 1 personil (misal, tiket pesawat individu)
        'deskripsi_biaya',       // Tambahan: Deskripsi lebih spesifik jika perlu (misal, "Uang Harian - Gol IV")
        'jumlah_personil_terkait',// Jumlah personil yang dicakup oleh item biaya ini (misal, 1 untuk uang harian individu, atau >1 jika biaya rombongan)
        'jumlah_hari_terkait',   // Jumlah hari yang relevan untuk item biaya ini (misal, lama perjalanan untuk uang harian)
        'jumlah_unit',           // Jumlah unit/kali (misal, 1 untuk tiket PP, atau jumlah malam untuk penginapan)
        'harga_satuan',          // Harga satuan dari SBU pada saat itu
        'subtotal_biaya',        // Hasil perhitungan (jumlah_personil_terkait * jumlah_hari_terkait * jumlah_unit * harga_satuan) - sesuaikan rumusnya
        'keterangan_tambahan',   // Catatan tambahan untuk item biaya ini
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'subtotal_biaya' => 'decimal:2',
        'jumlah_personil_terkait' => 'integer',
        'jumlah_hari_terkait' => 'integer',
        'jumlah_unit' => 'integer',
    ];

    /**
     * Get the perjalanan dinas that owns the biaya detail.
     */
    public function perjalananDinas(): BelongsTo
    {
        return $this->belongsTo(PerjalananDinas::class, 'perjalanan_dinas_id');
    }

    /**
     * Get the SBU item that this biaya detail refers to (jika Anda menyimpan referensi langsung ke SBU).
     * Jika tidak, field 'deskripsi_biaya' dan 'harga_satuan' akan menyimpan snapshot dari SBU.
     */
    public function sbuItem(): BelongsTo
    {
        // Pastikan kolom 'sbu_item_id' ada di tabel perjalanan_dinas_biaya jika menggunakan relasi ini
        return $this->belongsTo(SbuItem::class, 'sbu_item_id');
    }

    /**
     * Get the user associated with this specific cost (if applicable).
     * Ini berguna jika satu item biaya hanya berlaku untuk satu personil tertentu dalam rombongan.
     */
    public function userTerkait(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id_terkait');
    }
}