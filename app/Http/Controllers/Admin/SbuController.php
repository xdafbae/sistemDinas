<?php

namespace App\Http\Controllers\Admin;

use App\Models\SbuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel; // Import Facade Excel
use App\Imports\SbuItemsImport;      // Kita akan buat class Import ini
use Illuminate\Support\Facades\Validator; // Jika menggunakan Validator facade

class SbuController extends Controller
{
    public function __construct()
    {
        // Proteksi dengan permission atau role
        $this->middleware('can:manage sbu'); // Asumsi ada permission 'manage sbu'
        // Atau: $this->middleware('role:superadmin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SbuItem::select(['id', 'kategori_biaya', 'uraian_biaya', 'provinsi_tujuan', 'kota_tujuan', 'satuan', 'besaran_biaya', 'tipe_perjalanan', 'tingkat_pejabat_atau_golongan', 'keterangan', 'jarak_km_min', 'jarak_km_max']); // Pilih kolom yang dibutuhkan

            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('besaran_biaya', fn($row) => 'Rp ' . number_format($row->besaran_biaya, 0, ',', '.'))
                ->editColumn('kategori_biaya', fn($row) => ucfirst(str_replace('_', ' ', strtolower($row->kategori_biaya))))
                ->editColumn('tipe_perjalanan', fn($row) => $row->tipe_perjalanan ? ucfirst(str_replace('_', ' ', strtolower($row->tipe_perjalanan))) : '-')
                ->editColumn('tingkat_pejabat_atau_golongan', fn($row) => $row->tingkat_pejabat_atau_golongan ? ucfirst(str_replace('_', ' ', strtolower($row->tingkat_pejabat_atau_golongan))) : '-')
                ->addColumn('action', function ($row) {
                    $editUrl = route('admin.sbu.edit', $row->id);
                    $deleteForm = '
                        <form action="' . route('admin.sbu.destroy', $row->id) . '" method="POST" class="d-inline delete-form">
                            ' . csrf_field() . '
                            ' . method_field("DELETE") . '
                            <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Hapus SBU"><i class="fas fa-trash"></i></button>
                        </form>';
                    return '<a href="' . $editUrl . '" class="btn btn-secondary btn-sm me-1" data-bs-toggle="tooltip" title="Edit SBU"><i class="fas fa-edit"></i></a>' . $deleteForm;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.sbu.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Anda mungkin perlu mengirim data untuk dropdown jika ada (misal, daftar kategori SBU, provinsi, dll.)
        // Untuk contoh ini, kita asumsikan input teks atau select dengan opsi statis di form.
        return view('admin.sbu.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
            'keterangan' => 'nullable|string',
            'jarak_km_min' => 'nullable|integer|min:0',
            'jarak_km_max' => 'nullable|integer|min:0|gte:jarak_km_min',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.sbu.create')
                        ->withErrors($validator)
                        ->withInput();
        }

        SbuItem::create($request->all());

        return redirect()->route('admin.sbu.index')->with('success', 'Item SBU berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     * (Biasanya tidak diperlukan untuk manajemen SBU, daftar di index cukup)
     */
    public function show(SbuItem $sbuItem)
    {
        // return view('admin.sbu.show', compact('sbuItem'));
        return redirect()->route('admin.sbu.edit', $sbuItem->id); // Langsung ke edit
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SbuItem $sbuItem) // Route model binding
    {
        return view('admin.sbu.edit', compact('sbuItem'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SbuItem $sbuItem)
    {
         $validator = Validator::make($request->all(), [
            'kategori_biaya' => 'required|string|max:100',
            'uraian_biaya' => 'required|string|max:255',
            // ... (validasi lain sama seperti store) ...
            'jarak_km_max' => 'nullable|integer|min:0|gte:jarak_km_min',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.sbu.edit', $sbuItem->id)
                        ->withErrors($validator)
                        ->withInput();
        }

        $sbuItem->update($request->all());

        return redirect()->route('admin.sbu.index')->with('success', 'Item SBU berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SbuItem $sbuItem)
    {
        try {
            $sbuItem->delete();
            return redirect()->route('admin.sbu.index')->with('success', 'Item SBU berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Jika ada foreign key constraint yang menghalangi penghapusan
            Log::error("Gagal hapus SBU item ID {$sbuItem->id}: " . $e->getMessage());
            return redirect()->route('admin.sbu.index')->with('error', 'Gagal menghapus item SBU. Mungkin masih digunakan oleh data perjalanan dinas.');
        }
    }
    public function showImportForm()
    {
        // $this->authorize('manage sbu'); // Sudah di constructor
        return view('admin.sbu.import');
    }

    public function downloadSbuTemplate()
    {
        // $this->authorize('manage sbu'); // Sudah di constructor
        $headers = [
            'kategori_biaya', 'uraian_biaya', 'provinsi_tujuan', 'kota_tujuan', 'kecamatan_tujuan',
            'desa_tujuan', 'satuan', 'besaran_biaya', 'tipe_perjalanan',
            'tingkat_pejabat_atau_golongan', 'keterangan', 'jarak_km_min', 'jarak_km_max',
        ];
        $fileName = 'template_sbu_import.csv';

        // Buat konten CSV
        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            // Contoh baris data (opsional)
            // fputcsv($file, ['UANG_HARIAN', 'Uang Harian Luar Kota Aceh', 'ACEH', null, null, null, 'OH', 360000, 'LUAR_DAERAH_LUAR_KABUPATEN', 'Semua', null, null, null]);
            fclose($file);
        };
        $httpHeaders = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ];
        return response()->stream($callback, 200, $httpHeaders);
    }

    public function importSbu(Request $request)
    {
        // $this->authorize('manage sbu'); // Sudah di constructor
        $request->validate([
            'sbu_file' => 'required|file|mimes:csv,txt|max:5120', // Max 5MB
        ]);

        $sbuImportInstance = new SbuItemsImport();

        try {
            Excel::import($sbuImportInstance, $request->file('sbu_file'));

            $summary = $sbuImportInstance->getSummary();
            // Hitung ulang dari DB untuk kepastian setelah batch insert dan potensi error yang di-skip
            $actualImportedCount = SbuItem::count(); // Ini akan mengambil total di DB setelah import

            $validationFailures = $summary['validation_failures'];
            $otherErrors = $summary['other_errors'];
            $totalProcessedByModel = $summary['total_csv_data_rows']; // Baris data CSV yg coba di model()
            $modelsInstantiated = $summary['models_instantiated_count']; // Model yg berhasil dibuat instance-nya

            // Perkiraan yang berhasil diimpor oleh proses Maatwebsite (sebelum cek DB)
            $estimatedSuccessful = $modelsInstantiated - count($validationFailures) - count($otherErrors);
            $estimatedSuccessful = max(0, $estimatedSuccessful); // Pastikan tidak negatif

            $finalMessage = "Proses impor SBU selesai. " .
                            "Total baris data CSV coba diproses: {$totalProcessedByModel}. " .
                            "Perkiraan berhasil diimpor: {$estimatedSuccessful}. " .
                            "Total item SBU di database sekarang: {$actualImportedCount}.";


            if (!empty($validationFailures) || !empty($otherErrors)) {
                $allErrorMessages = array_merge($validationFailures, $otherErrors);
                return redirect()->route('admin.sbu.import.form')
                                 ->with('import_errors', $allErrorMessages)
                                 ->with('warning_toast', 'Beberapa data SBU gagal diimpor atau dilewati. Detail di bawah.')
                                 ->with('import_summary_text', $finalMessage); // Kirim teks summary
            }

            return redirect()->route('admin.sbu.index')
                             ->with('success', $finalMessage);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris " . $failure->row() . " (Header: ".$failure->attribute()."): " . implode(', ', $failure->errors()) . " (Nilai: " . json_encode($failure->values()[$failure->attribute()] ?? $failure->values()) . ")";
            }
            Log::error('Kesalahan validasi Maatwebsite Excel (ditangkap di Controller) saat import SBU CSV: ', $failures);
            return redirect()->back()->with('import_errors', $errorMessages)->withInput();
        } catch (\Exception $e) {
            Log::error('Kesalahan umum saat import SBU CSV (ditangkap di Controller): ' . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data SBU: ' . $e->getMessage());
        }
    }
}
