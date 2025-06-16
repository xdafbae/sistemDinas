<?php

namespace App\Imports;

use Throwable;
use App\Models\SbuItem;
use Illuminate\Support\Str;
use App\Rules\GteOtherField;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Log; // Pastikan ini ada
use Maatwebsite\Excel\Concerns\SkipsOnFailure;  // Akan melewati baris yang gagal validasi
use Maatwebsite\Excel\Concerns\SkipsOnError;    // Akan melewati error saat pembuatan model

class SbuItemsImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading
{
    use Importable;

    private int $csvRowCount = 0; // Nomor baris aktual di file CSV (termasuk header)
    private int $dataRowCount = 0; // Nomor baris data yang diproses (setelah header)
    private int $successfulModels = 0; // Jumlah model yang berhasil dibuat dan divalidasi
    public array $validationFailuresCollection = [];
    public array $errorRowsCollection = [];

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $this->dataRowCount++; // Baris data ke-X setelah header
        Log::info('SBU Import - Processing data row: ' . $this->dataRowCount . ' | Raw Data: ', $row);

        // Membersihkan dan memvalidasi 'besaran_biaya'
        $besaranBiayaStr = $row['besaran_biaya'] ?? '0';
        $besaranBiayaNumeric = null;
        if (is_numeric(str_replace(',', '.', str_replace('.', '', $besaranBiayaStr)))) { // Coba format Eropa: 1.234,56 -> 1234.56
            $besaranBiayaNumeric = floatval(str_replace(',', '.', str_replace('.', '', $besaranBiayaStr)));
        } elseif (is_numeric(str_replace(',', '', $besaranBiayaStr))) { // Coba format US/UK: 1,234.56 -> 1234.56
            $besaranBiayaNumeric = floatval(str_replace(',', '', $besaranBiayaStr));
        } else if (is_numeric($besaranBiayaStr)) { // Angka biasa tanpa pemisah ribuan
            $besaranBiayaNumeric = floatval($besaranBiayaStr);
        }

        if ($besaranBiayaNumeric === null) {
            Log::warning('SBU Import - Invalid numeric value for besaran_biaya on data row ' . $this->dataRowCount, ['original_value' => $row['besaran_biaya']]);
            // Validasi akan menangkap ini jika 'numeric' rule ada
        }

        // Parsing jarak_km_min dan jarak_km_max
        $jarakKmMin = null;
        if (isset($row['jarak_km_min']) && trim((string)$row['jarak_km_min']) !== '' && is_numeric($row['jarak_km_min'])) {
            $jarakKmMin = intval(trim((string)$row['jarak_km_min']));
        }

        $jarakKmMax = null;
        if (isset($row['jarak_km_max']) && trim((string)$row['jarak_km_max']) !== '' && is_numeric($row['jarak_km_max'])) {
            $jarakKmMax = intval(trim((string)$row['jarak_km_max']));
        }
        Log::info('SBU Import - PARSED Jarak for data row ' . $this->dataRowCount . ':', ['min' => $jarakKmMin, 'max' => $jarakKmMax]);

        try {
            $sbuItem = new SbuItem([
                'kategori_biaya'                  => Str::upper(str_replace(' ', '_', trim($row['kategori_biaya'] ?? ''))), // Normalisasi ke uppercase dan underscore
                'uraian_biaya'                    => trim($row['uraian_biaya'] ?? ''),
                'provinsi_tujuan'                 => empty(trim($row['provinsi_tujuan'] ?? '')) ? null : strtoupper(trim($row['provinsi_tujuan'])),
                'kota_tujuan'                     => empty(trim($row['kota_tujuan'] ?? '')) ? null : strtoupper(trim($row['kota_tujuan'])),
                'kecamatan_tujuan'                => empty(trim($row['kecamatan_tujuan'] ?? '')) ? null : Str::title(trim($row['kecamatan_tujuan'])),
                'desa_tujuan'                     => empty(trim($row['desa_tujuan'] ?? '')) ? null : Str::title(trim($row['desa_tujuan'])),
                'satuan'                          => trim($row['satuan'] ?? ''),
                'besaran_biaya'                   => $besaranBiayaAngka ?? 0, // Default 0 jika parsing gagal, validasi akan menangkap
                'tipe_perjalanan'                 => empty(trim($row['tipe_perjalanan'] ?? '')) ? null : Str::upper(str_replace(' ', '_', trim($row['tipe_perjalanan']))),
                'tingkat_pejabat_atau_golongan'   => empty(trim($row['tingkat_pejabat_atau_golongan'] ?? '')) ? null : Str::upper(str_replace(' ', '_', trim($row['tingkat_pejabat_atau_golongan']))),
                'keterangan'                      => empty(trim($row['keterangan'] ?? '')) ? null : trim($row['keterangan']),
                'jarak_km_min'                    => $jarakKmMin,
                'jarak_km_max'                    => $jarakKmMax,
            ]);

            // Jika lolos sampai sini (sebelum validasi formal dari rules()), model siap.
            // Validasi dari rules() akan dijalankan oleh Maatwebsite/Excel sebelum insert.
            $this->successfulModels++;
            Log::info('SBU Import - SbuItem instance to be created (data row ' . $this->dataRowCount . '):', $sbuItem->toArray());
            return $sbuItem;
        } catch (\Exception $e) {
            // Ini menangkap error saat pembuatan instance model itu sendiri (sebelum validasi rules)
            $errorMessage = "SBU Import - Exception during model instantiation for data row " . $this->dataRowCount . ": " . $e->getMessage();
            Log::error($errorMessage, ['row_data' => $row, 'exception_trace' => $e->getTraceAsString()]);
            $this->errorRowsCollection[] = "Baris data ke-" . $this->dataRowCount . ": Terjadi error internal saat memproses data - " . Str::limit($e->getMessage(), 100);
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'kategori_biaya' => 'required|string|max:100',
            'uraian_biaya' => 'required|string|max:255',
            'provinsi_tujuan' => 'nullable|string|max:100',
            'kota_tujuan' => 'nullable|string|max:100',
            'kecamatan_tujuan' => 'nullable|string|max:100',
            'desa_tujuan' => 'nullable|string|max:100',
            'satuan' => 'required|string|max:50',
            'besaran_biaya' => 'required|numeric|min:0',
            'tipe_perjalanan' => 'nullable|string|max:100',
            'tingkat_pejabat_atau_golongan' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string|max:1000',
            'jarak_km_min' => 'nullable|numeric|integer|min:0', // Pastikan ini divalidasi sebagai numerik dan integer
            'jarak_km_max' => ['nullable', 'numeric', 'integer', 'min:0', new GteOtherField('jarak_km_min')],
        ];
    }

    public function customValidationMessages()
    {
        return [
            // :row adalah nomor baris aktual di file (termasuk header jika ada)
            // :values_row adalah nomor baris data (setelah header)
            '*.required' => 'Kolom :attribute wajib diisi (Baris Data CSV ke-:values_row).',
            '*.string' => 'Kolom :attribute harus berupa teks (Baris Data CSV ke-:values_row).',
            '*.max' => 'Kolom :attribute tidak boleh lebih dari :max karakter (Baris Data CSV ke-:values_row).',
            '*.numeric' => 'Kolom :attribute harus berupa angka (Baris Data CSV ke-:values_row).',
            '*.integer' => 'Kolom :attribute harus berupa bilangan bulat (Baris Data CSV ke-:values_row).',
            '*.min' => 'Kolom :attribute minimal :min (Baris Data CSV ke-:values_row).',
            'jarak_km_max.gte' => 'Nilai untuk Jarak KM Maksimal harus lebih besar atau sama dengan nilai pada kolom Jarak KM Minimal (Baris Data CSV ke-:values_row).',
        ];
    }

    /**
     * Dipanggil jika SkipsOnError diimplementasikan dan terjadi Throwable saat proses model() atau penyimpanan.
     */
    public function onError(Throwable $e)
    {
        Log::error("SBU Import - ERROR DILEWATI (onError) pada baris data ke-" . $this->dataRowCount . ": " . $e->getMessage());
        $this->errorRowsCollection[] = "Baris data ke-" . $this->dataRowCount . ": Error database atau sistem - " . Str::limit($e->getMessage(), 100);
    }

    /**
     * Dipanggil jika SkipsOnFailure diimplementasikan dan validasi rules() gagal.
     * Method ini dipanggil untuk setiap baris yang gagal validasi.
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            // $failure->row() adalah nomor baris aktual di file CSV (termasuk header)
            // $this->dataRowCount adalah counter kita untuk baris data
            $csvDataRowNumber = $failure->row() - 1; // Asumsi header 1 baris
            $this->validationFailuresCollection[] = "Baris Data CSV ke-" . $csvDataRowNumber . ": Atribut '" . $failure->attribute() . "' - " . implode(', ', $failure->errors()) . " (Nilai: " . json_encode($failure->values()[$failure->attribute()] ?? $failure->values()) . ")";
        }
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function getSummary(): array
    {
        // dataRowCount adalah total baris data yang coba diproses oleh model()
        // successfulModels adalah berapa banyak model yang berhasil dibuat sebelum validasi/penyimpanan formal
        // validationFailuresCollection adalah error dari rules()
        // errorRowsCollection adalah error dari onError atau saat pembuatan model di try-catch
        $totalDataRowsAttempted = $this->dataRowCount;
        $failedDueToValidation = count($this->validationFailuresCollection);
        $failedDuringModelOrDb = count($this->errorRowsCollection);

        // Perhitungan yang lebih akurat untuk yang benar-benar masuk
        // Asumsi: jika validasi gagal, model tidak dibuat. Jika model error, tidak disimpan.
        // Jumlah yang berhasil adalah total yang coba diproses dikurangi yang gagal validasi dan error lainnya.
        // Ini bisa jadi lebih kompleks jika SkipsOnError dan SkipsOnFailure berinteraksi.
        // Untuk simple, kita anggap yang dihitung di $this->successfulModels sudah melewati tahap awal.
        // Mari kita hitung dari yang berhasil di-return oleh model().
        $actuallyImported = $this->successfulModels - $failedDueToValidation - $failedDuringModelOrDb;
        // Perlu dipastikan bahwa $successfulModels hanya di-increment jika model valid dan siap disimpan.

        return [
            'total_csv_data_rows' => $this->dataRowCount,
            'models_instantiated_count' => $this->successfulModels, // Berapa banyak instance model dibuat (sebelum insert)
            'validation_failures' => $this->validationFailuresCollection,
            'other_errors' => $this->errorRowsCollection,
            // 'actually_imported_to_db' => $actuallyImported > 0 ? $actuallyImported : 0, // Ini perlu diverifikasi lebih lanjut
        ];
    }
}
