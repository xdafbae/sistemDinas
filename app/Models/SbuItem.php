<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SbuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'kategori_biaya',
        'uraian_biaya',
        'provinsi_tujuan',
        'kota_tujuan',          // <--- Ada di sini
        'kecamatan_tujuan',     // <--- Ada di sini
        'desa_tujuan',          // <--- Ada di sini
        'satuan',
        'besaran_biaya',
        'tipe_perjalanan',
        'tingkat_pejabat_atau_golongan',
        'keterangan',           // <--- Ada di sini
        'jarak_km_min',         // <--- Ada di sini
        'jarak_km_max'
    ];

    protected $casts = [
        'besaran_biaya' => 'decimal:2',
    ];
}
