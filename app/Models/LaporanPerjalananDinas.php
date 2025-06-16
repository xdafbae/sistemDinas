<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaporanPerjalananDinas extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'laporan_perjalanan_dinas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'perjalanan_dinas_id',
        'user_id', // User yang membuat/bertanggung jawab atas laporan ini
        'tanggal_laporan',
        'ringkasan_hasil_kegiatan',
        'kendala_dihadapi',
        'saran_tindak_lanjut',
        'status_laporan',
        'catatan_pereview', // Catatan dari Bendahara/Verifikator Laporan
        'total_biaya_rill_dilaporkan',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tanggal_laporan' => 'date',
        'total_biaya_rill_dilaporkan' => 'decimal:2',
    ];

    /**
     * Get the perjalanan dinas that this report belongs to.
     */
    public function perjalananDinas(): BelongsTo
    {
        return $this->belongsTo(PerjalananDinas::class, 'perjalanan_dinas_id');
    }

    /**
     * Get the user who created this report.
     */
    public function pelapor(): BelongsTo // Menggunakan 'pelapor' agar lebih deskriptif
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the rincian biaya riil for this report.
     */
    public function rincianBiaya(): HasMany
    {
        return $this->hasMany(LaporanPerjalananDinasRincianBiaya::class, 'laporan_perjalanan_dinas_id');
    }

    // Anda bisa menambahkan scope atau method lain di sini jika diperlukan
    // Contoh scope untuk laporan yang menunggu verifikasi:
    // public function scopeMenungguVerifikasi($query)
    // {
    //     return $query->where('status_laporan', 'diserahkan_untuk_verifikasi');
    // }
}