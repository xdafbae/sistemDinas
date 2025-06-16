<?php

namespace App\Http\Controllers\Persetujuan; // Sesuaikan namespace

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\PerjalananDinas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class PersetujuanPerjalananDinasController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:atasan|superadmin']); // Hanya untuk Atasan
    }

    /**
     * Menampilkan daftar perjalanan dinas yang perlu disetujui.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PerjalananDinas::with(['operator', 'personil', 'verifikator'])
                        ->where('status', 'menunggu_persetujuan_atasan') // Hanya yang menunggu persetujuan atasan
                        ->latest();

            // Jika ada logika Atasan hanya bisa approve OPD tertentu, tambahkan filter di sini
            // $user = Auth::user();
            // if ($user->opd_id) { // Asumsi user atasan punya relasi ke OPD
            //     $query->whereHas('operator', function($q) use ($user) {
            //         $q->where('opd_id', $user->opd_id);
            //     });
            // }


            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('operator_nama', fn($pd) => $pd->operator->nama ?? '-')
                ->addColumn('verifikator_nama', fn($pd) => $pd->verifikator->nama ?? '-')
                ->addColumn('personil_list', fn($pd) => $pd->personil->pluck('nama')->implode(', '))
                ->addColumn('tanggal_spt_formatted', fn($pd) => Carbon::parse($pd->tanggal_spt)->translatedFormat('d M Y'))
                ->addColumn('tanggal_pelaksanaan', fn($pd) => Carbon::parse($pd->tanggal_mulai)->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($pd->tanggal_selesai)->translatedFormat('d M Y'))
                ->editColumn('total_estimasi_biaya', fn($pd) => 'Rp ' . number_format($pd->total_estimasi_biaya, 0, ',', '.'))
                ->addColumn('action', function ($perjalanan) {
                    $showUrl = route('atasan.perjalanan-dinas.show', $perjalanan->id);
                    return '<a href="' . $showUrl . '" class="btn btn-sm btn-success"><i class="fas fa-check-circle"></i> Proses Persetujuan</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('perjalanan_dinas.atasan.index');
    }

    /**
     * Menampilkan detail perjalanan dinas untuk disetujui.
     */
   public function show(PerjalananDinas $perjalananDinas)
    {
        // Otorisasi lebih lanjut bisa ditambahkan dengan Policy
        // $this->authorize('approve', $perjalananDinas);

        if ($perjalananDinas->status !== 'menunggu_persetujuan_atasan') {
            return redirect()->route('atasan.perjalanan-dinas.index')->with('error', 'Pengajuan ini tidak lagi menunggu persetujuan Anda atau sudah diproses.');
        }
        $perjalananDinas->load(['personil', 'operator', 'verifikator', 'biayaDetails.sbuItem', 'biayaDetails.userTerkait']);
        return view('perjalanan_dinas.atasan.show', compact('perjalananDinas'));
    }

    /**
     * Memproses aksi persetujuan (setuju atau revisi/tolak).
     */
    public function processApproval(Request $request, PerjalananDinas $perjalananDinas)
    {
        // Otorisasi lebih lanjut bisa ditambahkan dengan Policy
        // $this->authorize('approve', $perjalananDinas);

        if ($perjalananDinas->status !== 'menunggu_persetujuan_atasan') {
            return redirect()->route('atasan.perjalanan-dinas.index')->with('error', 'Pengajuan ini tidak lagi menunggu persetujuan Anda atau sudah diproses.');
        }

        $validator = Validator::make($request->all(), [
            'aksi_persetujuan' => 'required|in:setuju,revisi,tolak',
            'catatan_atasan' => 'nullable|string|required_if:aksi_persetujuan,revisi|required_if:aksi_persetujuan,tolak|max:1000',
            'tanggal_spt_final' => 'required_if:aksi_persetujuan,setuju|nullable|date|before_or_equal:today', // Tanggal SPT tidak boleh di masa depan
        ]);

        if ($validator->fails()) {
            return redirect()->route('atasan.perjalanan-dinas.show', $perjalananDinas->id)
                        ->withErrors($validator)
                        ->withInput();
        }

        DB::beginTransaction();
        try {
            $perjalananDinas->atasan_id = Auth::id();
            $perjalananDinas->catatan_atasan = $request->catatan_atasan;
            $message = '';
            $nomorSptFinalGenerated = $perjalananDinas->nomor_spt;

            if ($request->aksi_persetujuan == 'setuju') {
                if (empty($perjalananDinas->nomor_spt)) {
                    // Ambil tanggal SPT final untuk menentukan tahun jika nomor urut per tahun
                    $tanggalSPTUntukNomor = Carbon::parse($request->tanggal_spt_final);
                    $tahunSPT = $tanggalSPTUntukNomor->year;

                    // 1. Tentukan Kode Klasifikasi/Bagian Depan
                    // Ini bisa di-hardcode, atau lebih baik dari file konfigurasi
                    $kodeBagianDepan = config('constants.spt.kode_klasifikasi_awal', '000.1.2.3');
                    // Alternatif jika tidak pakai config:
                    // $kodeBagianDepan = "000.1.2.3";

                    // 2. Tentukan Teks Tengah
                    $teksTengah = "SPT";

                    // 3. Generate Nomor Urut
                    // Nomor urut bisa berdasarkan total SPT yang sudah ada nomornya di tahun berjalan
                    // atau nomor urut yang disimpan terpisah dan di-increment.
                    // Contoh: nomor urut berdasarkan jumlah SPT yang sudah ada di tahun berjalan
                    $nomorUrut = PerjalananDinas::whereNotNull('nomor_spt')
                                        ->whereYear('tanggal_spt', $tahunSPT) // Berdasarkan tahun dari tanggal SPT final
                                        ->count() + 1;

                    // Format nomor urut (misalnya, tanpa padding nol di depan jika contohnya 744 bukan 001)
                    // Jika Anda ingin padding seperti 001, 002, ..., gunakan: sprintf("%03d", $nomorUrut)

                    $nomorSptFinal = sprintf("%s/%s/%d", $kodeBagianDepan, $teksTengah, $nomorUrut);

                    // Double check keunikan nomor SPT (penting jika ada potensi konkurensi)
                    $counterUnik = 1;
                    $originalNomorSptFinal = $nomorSptFinal;
                    while (PerjalananDinas::where('nomor_spt', $nomorSptFinal)->where('id', '!=', $perjalananDinas->id)->exists()) {
                        // Jika nomor sudah ada, coba tambahkan suffix atau increment bagian nomor urut
                        // Ini contoh sederhana, Anda mungkin perlu strategi yang lebih baik
                        $nomorUrut++; // Increment nomor urut global tahunan
                        $nomorSptFinal = sprintf("%s/%s/%d", $kodeBagianDepan, $teksTengah, $nomorUrut);
                        if($counterUnik++ > 20) { // Failsafe untuk menghindari infinite loop
                            Log::error("Gagal generate nomor SPT unik setelah banyak percobaan untuk Perjadin ID: {$perjalananDinas->id}");
                            throw new \Exception("Gagal generate nomor SPT unik.");
                        }
                    }
                    $perjalananDinas->nomor_spt = $nomorSptFinal;
                    $nomorSptFinalGenerated = $nomorSptFinal;
                }

                $perjalananDinas->tanggal_spt = $request->tanggal_spt_final;
                $perjalananDinas->status = 'selesai';
                $message = 'Pengajuan perjalanan dinas berhasil disetujui dengan Nomor SPT: ' . $nomorSptFinalGenerated;

            } elseif ($request->aksi_persetujuan == 'revisi') {
                $perjalananDinas->status = 'revisi_operator_atasan';
                $message = 'Pengajuan dikembalikan ke Operator untuk revisi dari Atasan.';
            } elseif ($request->aksi_persetujuan == 'tolak') {
                $perjalananDinas->status = 'ditolak_atasan';
                $message = 'Pengajuan ditolak oleh Atasan.';
                // TODO: Kirim notifikasi ke Operator
            }

            $perjalananDinas->save();
            DB::commit();
            return redirect()->route('atasan.perjalanan-dinas.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saat proses persetujuan atasan: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Gagal memproses persetujuan: Terjadi kesalahan internal. Detail: '. $e->getMessage())->withInput();
        }
    }

    // Helper untuk bulan Romawi
    private function getRomanMonth($monthNumber) {
        $map = array(1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII');
        return $map[intval($monthNumber)] ?? '';
    }
}