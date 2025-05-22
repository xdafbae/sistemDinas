<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SbuItem; // Pastikan model SbuItem sudah ada
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Untuk timestamp

class SbuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nonaktifkan cek foreign key sementara untuk truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SbuItem::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Definisikan semua kemungkinan key yang ada di tabel sbu_items
        // Ini akan digunakan untuk memastikan setiap item data memiliki struktur yang sama
        $defaultKeys = [
            'kategori_biaya' => null,
            'uraian_biaya' => null,
            'provinsi_tujuan' => null,
            'kota_tujuan' => null,
            'kecamatan_tujuan' => null,
            'desa_tujuan' => null,
            'satuan' => null,
            'besaran_biaya' => 0, // Default ke 0 jika tidak diset
            'tipe_perjalanan' => null,
            'tingkat_pejabat_atau_golongan' => null,
            'keterangan' => null,
            'jarak_km_min' => null,
            'jarak_km_max' => null,
            'created_at' => Carbon::now(), // Otomatis isi timestamp
            'updated_at' => Carbon::now(), // Otomatis isi timestamp
        ];

        $sbuData = [
            // ==================================================================
            // 1. UANG HARIAN LUAR KABUPATEN (Hal 1)
            // ==================================================================
            // ACEH
            array_merge($defaultKeys, ['kategori_biaya' => 'UANG_HARIAN', 'uraian_biaya' => 'Luar Kabupaten Aceh', 'provinsi_tujuan' => 'ACEH', 'satuan' => 'OH', 'besaran_biaya' => 360000, 'tipe_perjalanan' => 'LUAR_DAERAH_LUAR_KABUPATEN', 'tingkat_pejabat_atau_golongan' => 'Semua']),
            array_merge($defaultKeys, ['kategori_biaya' => 'UANG_HARIAN', 'uraian_biaya' => 'Diklat Aceh', 'provinsi_tujuan' => 'ACEH', 'satuan' => 'OH', 'besaran_biaya' => 110000, 'tipe_perjalanan' => 'DIKLAT', 'tingkat_pejabat_atau_golongan' => 'Semua']),
            // SUMATERA UTARA
            array_merge($defaultKeys, ['kategori_biaya' => 'UANG_HARIAN', 'uraian_biaya' => 'Luar Kabupaten Sumatera Utara', 'provinsi_tujuan' => 'SUMATERA UTARA', 'satuan' => 'OH', 'besaran_biaya' => 370000, 'tipe_perjalanan' => 'LUAR_DAERAH_LUAR_KABUPATEN', 'tingkat_pejabat_atau_golongan' => 'Semua']),
            array_merge($defaultKeys, ['kategori_biaya' => 'UANG_HARIAN', 'uraian_biaya' => 'Diklat Sumatera Utara', 'provinsi_tujuan' => 'SUMATERA UTARA', 'satuan' => 'OH', 'besaran_biaya' => 110000, 'tipe_perjalanan' => 'DIKLAT', 'tingkat_pejabat_atau_golongan' => 'Semua']),
            // RIAU
            array_merge($defaultKeys, ['kategori_biaya' => 'UANG_HARIAN', 'uraian_biaya' => 'Luar Kabupaten Riau', 'provinsi_tujuan' => 'RIAU', 'satuan' => 'OH', 'besaran_biaya' => 370000, 'tipe_perjalanan' => 'LUAR_DAERAH_LUAR_KABUPATEN', 'tingkat_pejabat_atau_golongan' => 'Semua']),
            array_merge($defaultKeys, ['kategori_biaya' => 'UANG_HARIAN', 'uraian_biaya' => 'Diklat Riau', 'provinsi_tujuan' => 'RIAU', 'satuan' => 'OH', 'besaran_biaya' => 110000, 'tipe_perjalanan' => 'DIKLAT', 'tingkat_pejabat_atau_golongan' => 'Semua']),
            // D.K.I. JAKARTA
            array_merge($defaultKeys, ['kategori_biaya' => 'UANG_HARIAN', 'uraian_biaya' => 'Luar Kabupaten D.K.I. Jakarta', 'provinsi_tujuan' => 'D.K.I. JAKARTA', 'satuan' => 'OH', 'besaran_biaya' => 530000, 'tipe_perjalanan' => 'LUAR_DAERAH_LUAR_KABUPATEN', 'tingkat_pejabat_atau_golongan' => 'Semua']),
            array_merge($defaultKeys, ['kategori_biaya' => 'UANG_HARIAN', 'uraian_biaya' => 'Diklat D.K.I. Jakarta', 'provinsi_tujuan' => 'D.K.I. JAKARTA', 'satuan' => 'OH', 'besaran_biaya' => 160000, 'tipe_perjalanan' => 'DIKLAT', 'tingkat_pejabat_atau_golongan' => 'Semua']),
            // PAPUA BARAT
            array_merge($defaultKeys, ['kategori_biaya' => 'UANG_HARIAN', 'uraian_biaya' => 'Luar Kabupaten Papua Barat', 'provinsi_tujuan' => 'PAPUA BARAT', 'satuan' => 'OH', 'besaran_biaya' => 480000, 'tipe_perjalanan' => 'LUAR_DAERAH_LUAR_KABUPATEN', 'tingkat_pejabat_atau_golongan' => 'Semua']),
            array_merge($defaultKeys, ['kategori_biaya' => 'UANG_HARIAN', 'uraian_biaya' => 'Diklat Papua Barat', 'provinsi_tujuan' => 'PAPUA BARAT', 'satuan' => 'OH', 'besaran_biaya' => 140000, 'tipe_perjalanan' => 'DIKLAT', 'tingkat_pejabat_atau_golongan' => 'Semua']),
            // (!!! LENGKAPI SEMUA 34 PROVINSI UNTUK UANG HARIAN LUAR KABUPATEN DAN DIKLAT !!!)

            // ==================================================================
            // 2. UANG HARIAN DALAM KABUPATEN > 8 JAM (Hal 1 bawah)
            // ==================================================================
            array_merge($defaultKeys, ['kategori_biaya' => 'UANG_HARIAN', 'uraian_biaya' => 'Dalam Kabupaten Riau > 8 Jam', 'provinsi_tujuan' => 'RIAU', 'kota_tujuan' => 'SIAK', 'satuan' => 'OH', 'besaran_biaya' => 150000, 'tipe_perjalanan' => 'DALAM_KABUPATEN_LEBIH_8_JAM', 'tingkat_pejabat_atau_golongan' => 'Semua']),

            // ==================================================================
            // 3. UANG REPRESENTASI (Hal 2)
            // ==================================================================
            array_merge($defaultKeys, ['kategori_biaya' => 'REPRESENTASI', 'uraian_biaya' => 'Pejabat Negara/Daerah Luar Kabupaten',   'satuan' => 'OH', 'besaran_biaya' => 250000, 'tipe_perjalanan' => 'LUAR_DAERAH_LUAR_KABUPATEN', 'tingkat_pejabat_atau_golongan' => 'KEPALA_DAERAH_PIMPINAN_DPRD']),
            array_merge($defaultKeys, ['kategori_biaya' => 'REPRESENTASI', 'uraian_biaya' => 'Pejabat Negara/Daerah Dalam Kabupaten',  'satuan' => 'OH', 'besaran_biaya' => 125000, 'tipe_perjalanan' => 'DALAM_KABUPATEN_LEBIH_8_JAM', 'tingkat_pejabat_atau_golongan' => 'KEPALA_DAERAH_PIMPINAN_DPRD']),
            array_merge($defaultKeys, ['kategori_biaya' => 'REPRESENTASI', 'uraian_biaya' => 'Pejabat Eselon I Luar Kabupaten',        'satuan' => 'OH', 'besaran_biaya' => 200000, 'tipe_perjalanan' => 'LUAR_DAERAH_LUAR_KABUPATEN', 'tingkat_pejabat_atau_golongan' => 'ESELON_I']),
            array_merge($defaultKeys, ['kategori_biaya' => 'REPRESENTASI', 'uraian_biaya' => 'Pejabat Eselon I Dalam Kabupaten',       'satuan' => 'OH', 'besaran_biaya' => 100000, 'tipe_perjalanan' => 'DALAM_KABUPATEN_LEBIH_8_JAM', 'tingkat_pejabat_atau_golongan' => 'ESELON_I']),
            array_merge($defaultKeys, ['kategori_biaya' => 'REPRESENTASI', 'uraian_biaya' => 'Pejabat Eselon II Luar Kabupaten',       'satuan' => 'OH', 'besaran_biaya' => 150000, 'tipe_perjalanan' => 'LUAR_DAERAH_LUAR_KABUPATEN', 'tingkat_pejabat_atau_golongan' => 'ESELON_II']),
            array_merge($defaultKeys, ['kategori_biaya' => 'REPRESENTASI', 'uraian_biaya' => 'Pejabat Eselon II Dalam Kabupaten',      'satuan' => 'OH', 'besaran_biaya' => 75000,  'tipe_perjalanan' => 'DALAM_KABUPATEN_LEBIH_8_JAM', 'tingkat_pejabat_atau_golongan' => 'ESELON_II']),

            // ==================================================================
            // 4. TARIF HOTEL LUAR DAERAH LUAR KABUPATEN (Hal 3 & 4 atas)
            // ==================================================================
            // ACEH (Contoh)
            array_merge($defaultKeys, ['kategori_biaya' => 'PENGINAPAN', 'uraian_biaya' => 'Hotel Aceh Kepala Daerah/Eselon I', 'provinsi_tujuan' => 'ACEH', 'satuan' => 'OH', 'besaran_biaya' => 4420000, 'tipe_perjalanan' => 'LUAR_DAERAH_LUAR_KABUPATEN', 'tingkat_pejabat_atau_golongan' => 'KEPALA_DAERAH_ESELON_I']),
            array_merge($defaultKeys, ['kategori_biaya' => 'PENGINAPAN', 'uraian_biaya' => 'Hotel Aceh Anggota DPRD/Eselon II', 'provinsi_tujuan' => 'ACEH', 'satuan' => 'OH', 'besaran_biaya' => 3526000, 'tipe_perjalanan' => 'LUAR_DAERAH_LUAR_KABUPATEN', 'tingkat_pejabat_atau_golongan' => 'ESELON_II']),
            // (!!! LENGKAPI SEMUA PROVINSI DAN SEMUA TINGKAT/GOLONGAN UNTUK TARIF HOTEL !!!)

            // ==================================================================
            // E. PENGINAPAN DALAM KABUPATEN DIATAS 8 JAM (Hal 4 bawah)
            // ==================================================================
            array_merge($defaultKeys, ['kategori_biaya' => 'PENGINAPAN', 'uraian_biaya' => 'Hotel Kab. Siak Kepala Daerah/Eselon I',  'provinsi_tujuan' => 'RIAU', 'kota_tujuan' => 'SIAK', 'satuan' => 'OH', 'besaran_biaya' => 800000, 'tipe_perjalanan' => 'DALAM_KABUPATEN_LEBIH_8_JAM', 'tingkat_pejabat_atau_golongan' => 'KEPALA_DAERAH_ESELON_I']),
            array_merge($defaultKeys, ['kategori_biaya' => 'PENGINAPAN', 'uraian_biaya' => 'Hotel Kab. Siak Anggota DPRD/Eselon II',    'provinsi_tujuan' => 'RIAU', 'kota_tujuan' => 'SIAK', 'satuan' => 'OH', 'besaran_biaya' => 650000, 'tipe_perjalanan' => 'DALAM_KABUPATEN_LEBIH_8_JAM', 'tingkat_pejabat_atau_golongan' => 'ESELON_II']),
            array_merge($defaultKeys, ['kategori_biaya' => 'PENGINAPAN', 'uraian_biaya' => 'Hotel Kab. Siak Eselon III/Golongan IV',  'provinsi_tujuan' => 'RIAU', 'kota_tujuan' => 'SIAK', 'satuan' => 'OH', 'besaran_biaya' => 480000, 'tipe_perjalanan' => 'DALAM_KABUPATEN_LEBIH_8_JAM', 'tingkat_pejabat_atau_golongan' => 'ESELON_III_GOL_IV']),
            array_merge($defaultKeys, ['kategori_biaya' => 'PENGINAPAN', 'uraian_biaya' => 'Hotel Kab. Siak Eselon IV/Golongan III',  'provinsi_tujuan' => 'RIAU', 'kota_tujuan' => 'SIAK', 'satuan' => 'OH', 'besaran_biaya' => 400000, 'tipe_perjalanan' => 'DALAM_KABUPATEN_LEBIH_8_JAM', 'tingkat_pejabat_atau_golongan' => 'ESELON_IV_GOL_III']),
            array_merge($defaultKeys, ['kategori_biaya' => 'PENGINAPAN', 'uraian_biaya' => 'Hotel Kab. Siak Golongan II/I & Non ASN', 'provinsi_tujuan' => 'RIAU', 'kota_tujuan' => 'SIAK', 'satuan' => 'OH', 'besaran_biaya' => 300000, 'tipe_perjalanan' => 'DALAM_KABUPATEN_LEBIH_8_JAM', 'tingkat_pejabat_atau_golongan' => 'GOL_II_I_NON_ASN']),

            // ==================================================================
            // TRANSPORTASI DARAT (TAKSI) DALAM NEGERI (Hal 5)
            // ==================================================================
            array_merge($defaultKeys, ['kategori_biaya' => 'TRANSPORTASI_DARAT_TAKSI', 'uraian_biaya' => 'Taksi Aceh PP', 'provinsi_tujuan' => 'ACEH', 'satuan' => 'KALI', 'besaran_biaya' => 133000, 'tipe_perjalanan' => 'LUAR_DAERAH_LUAR_KABUPATEN', 'tingkat_pejabat_atau_golongan' => 'Semua']),
            // (!!! LENGKAPI SEMUA PROVINSI UNTUK TAKSI !!!)

            // ==================================================================
            // BIAYA TRANSPORTASI DARI SIAK KE KECAMATAN (Hal 6) - Dua Kali Jalan (PP)
            // ==================================================================
            array_merge($defaultKeys, ['kategori_biaya' => 'TRANSPORTASI_KECAMATAN_DESA', 'uraian_biaya' => 'Siak Sri Indrapura ke Dayun (Ibukota Kecamatan)', 'provinsi_tujuan' => 'RIAU', 'kota_tujuan' => 'SIAK', 'kecamatan_tujuan' => 'Dayun', 'satuan' => 'PP', 'besaran_biaya' => 65000,  'tipe_perjalanan' => 'DALAM_KABUPATEN', 'tingkat_pejabat_atau_golongan' => 'Semua']),
            // (!!! LENGKAPI SEMUA KECAMATAN DAN DESA/PEDALAMAN DARI HALAMAN 6-8 !!!)
            // Contoh berdasarkan jarak
            array_merge($defaultKeys, ['kategori_biaya' => 'TRANSPORTASI_KECAMATAN_DESA', 'uraian_biaya' => 'Transportasi Dalam Siak 0-5 Km', 'provinsi_tujuan' => 'RIAU', 'kota_tujuan' => 'SIAK', 'kecamatan_tujuan' => 'SIAK', 'jarak_km_min' => 0, 'jarak_km_max' => 5, 'satuan' => 'PP', 'besaran_biaya' => 50000, 'tipe_perjalanan' => 'DALAM_KABUPATEN', 'tingkat_pejabat_atau_golongan' => 'Semua', 'keterangan' => '0 Km - < 10 Km Rp 50.000,- (dari SBU hal 6)']),

            // ==================================================================
            // TRANSPORTASI DARAT DARI SIAK KE KABUPATEN/KOTA LAIN DI RIAU (PP) (Hal 9 atas)
            // ==================================================================
            array_merge($defaultKeys, ['kategori_biaya' => 'TRANSPORTASI_ANTAR_KABUPATEN_PROVINSI', 'uraian_biaya' => 'Siak ke Pekanbaru PP', 'provinsi_tujuan' => 'RIAU', 'kota_tujuan' => 'PEKANBARU', 'satuan' => 'PP', 'besaran_biaya' => 350000, 'tipe_perjalanan' => 'LUAR_DAERAH_DALAM_PROVINSI', 'tingkat_pejabat_atau_golongan' => 'Semua']),
            // (!!! LENGKAPI SEMUA KAB/KOTA DI RIAU DARI HALAMAN 9 ATAS !!!)

            // ==================================================================
            // TRANSPORTASI UDARA DARI PEKANBARU KE IBUKOTA NEGARA/PROPINSI LAIN (PP) (Hal 9 bawah)
            // ==================================================================
            array_merge($defaultKeys, ['kategori_biaya' => 'TRANSPORTASI_UDARA', 'uraian_biaya' => 'Pekanbaru ke Aceh (Bisnis PP)', 'provinsi_tujuan' => 'ACEH', 'satuan' => 'Tiket', 'besaran_biaya' => 6500000, 'tipe_perjalanan' => 'LUAR_DAERAH_LUAR_PROVINSI', 'tingkat_pejabat_atau_golongan' => 'BISNIS']),
            array_merge($defaultKeys, ['kategori_biaya' => 'TRANSPORTASI_UDARA', 'uraian_biaya' => 'Pekanbaru ke Aceh (Ekonomi PP)', 'provinsi_tujuan' => 'ACEH', 'satuan' => 'Tiket', 'besaran_biaya' => 3700000, 'tipe_perjalanan' => 'LUAR_DAERAH_LUAR_PROVINSI', 'tingkat_pejabat_atau_golongan' => 'EKONOMI']),
            // (!!! LENGKAPI SEMUA TUJUAN DAN KELAS TIKET DARI HALAMAN 9 BAWAH !!!)

        ];

        // Chunk data untuk insert yang lebih efisien
        // Tidak perlu lagi array_map di sini karena $defaultKeys sudah di-merge di setiap item
        foreach (array_chunk($sbuData, 200) as $chunk) {
            SbuItem::insert($chunk);
        }

        $this->command->info('Tabel SBU Items berhasil diisi dengan data contoh!');
        $this->command->warn('PERHATIAN: Seeder ini belum mencakup semua data SBU dari dokumen. Silakan lengkapi secara manual.');
    }
}