<?php

namespace App\Helpers; // <--- PASTIKAN NAMESPACE INI BENAR

class TerbilangHelper
{
    protected static $angka = [
        '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'
    ];

    protected static $level = [
        '', '', 'ribu', 'juta', 'milyar', 'triliun'
    ];

    /**
     * Mengubah angka menjadi terbilang dalam bahasa Indonesia.
     *
     * @param int|float $nilai Angka yang akan diubah.
     * @param int $levelPenyebut Level penyebut (untuk ribuan, jutaan, dst.).
     * @return string Terbilang dari angka.
     */
    public static function angka($nilai, $levelPenyebut = 0): string
    {
        $nilai = abs(floatval($nilai)); // Ambil nilai absolut dan pastikan float
        $terbilang = '';

        if ($nilai < 12) {
            $terbilang = ' ' . static::$angka[intval($nilai)];
        } elseif ($nilai < 20) {
            $terbilang = static::angka($nilai - 10) . ' belas';
        } elseif ($nilai < 100) {
            $terbilang = static::angka($nilai / 10) . ' puluh' . static::angka($nilai % 10);
        } elseif ($nilai < 200) {
            $terbilang = ' seratus' . static::angka($nilai - 100);
        } elseif ($nilai < 1000) {
            $terbilang = static::angka($nilai / 100) . ' ratus' . static::angka($nilai % 100);
        } elseif ($nilai < 2000) {
            $terbilang = ' seribu' . static::angka($nilai - 1000);
        } elseif ($nilai < 1000000) {
            $terbilang = static::angka($nilai / 1000) . ' ribu' . static::angka($nilai % 1000);
        } elseif ($nilai < 1000000000) {
            $terbilang = static::angka($nilai / 1000000) . ' juta' . static::angka($nilai % 1000000);
        } elseif ($nilai < 1000000000000) {
            $terbilang = static::angka($nilai / 1000000000) . ' milyar' . static::angka($nilai % 1000000000);
        } elseif ($nilai < 1000000000000000) {
            $terbilang = static::angka($nilai / 1000000000000) . ' triliun' . static::angka($nilai % 1000000000000);
        }

        // Menghilangkan spasi ganda dan merapikan output
        return trim(str_replace('  ', ' ', $terbilang));
    }

    /**
     * Fungsi utama untuk mengkonversi angka menjadi terbilang dengan format yang lebih rapi.
     *
     * @param int|float $angka
     * @return string
     */
    public static function terbilang($angka): string
    {
        if ($angka === null || $angka === '') {
            return '';
        }

        if ($angka < 0) {
            $hasil = 'minus ' . trim(static::angka($angka));
        } else {
            $hasil = trim(static::angka($angka));
        }

        // Kapitalisasi huruf pertama dari setiap kata
        return ucwords($hasil);
    }
}