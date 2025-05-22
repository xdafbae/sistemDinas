<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// Pastikan use statement ini ada jika belum
use App\Models\PerjalananDinasBiaya;
use App\Models\User; // Pastikan User model juga di-import jika belum

class PerjalananDinas extends Model
{
    use HasFactory;

    protected $table = 'perjalanan_dinas';

    protected $fillable = [
        'nomor_spt',
        'tanggal_spt',
        'jenis_spt',
        'jenis_kegiatan',
        'tujuan_spt',
        'provinsi_tujuan_id',
        'kota_tujuan_id',
        'dasar_spt',
        'uraian_spt',
        'alat_angkut', 
        'lama_hari',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'catatan_verifikator',
        'catatan_atasan',
        'operator_id',
        'verifikator_id',
        'atasan_id',
        'total_estimasi_biaya',
        'total_biaya_rill',
    ];

    protected $casts = [
        'tanggal_spt' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'total_estimasi_biaya' => 'decimal:2',
        'total_biaya_rill' => 'decimal:2',
    ];

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifikator_id');
    }

    public function atasan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    public function personil(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'perjalanan_dinas_personil', 'perjalanan_dinas_id', 'user_id')
            ->withTimestamps(); // Jika tabel pivot Anda memiliki timestamps
    }

    /**
     * Get the biaya details for the Perjalanan Dinas.
     */
    public function biayaDetails(): HasMany
    {
        // Ini adalah relasi yang menggunakan model PerjalananDinasBiaya
        return $this->hasMany(PerjalananDinasBiaya::class, 'perjalanan_dinas_id');
    }
}
