<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanPerjalananDinasRincianBiaya extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'laporan_perjalanan_dinas_rincian_biaya';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'laporan_perjalanan_dinas_id',
        // 'perjalanan_dinas_biaya_id', // Opsional: Jika ingin melacak ke estimasi biaya awal
        'sbu_item_id',               // Opsional: Jika ingin melacak ke item SBU spesifik
        'deskripsi_biaya_rill',
        'jumlah_rill',
        'satuan_rill',
        'harga_satuan_rill',
        'subtotal_biaya_rill',
        'nomor_bukti',
        'path_bukti_file',
        'keterangan_rill',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'harga_satuan_rill' => 'decimal:2',
        'subtotal_biaya_rill' => 'decimal:2',
        'jumlah_rill' => 'integer',
    ];

    /**
     * Get the main report that this rincian belongs to.
     */
    public function laporanPerjalananDinas(): BelongsTo
    {
        return $this->belongsTo(LaporanPerjalananDinas::class, 'laporan_perjalanan_dinas_id');
    }

    
    public function sbuItem(): BelongsTo
    {
        return $this->belongsTo(SbuItem::class, 'sbu_item_id');
    }

    
}