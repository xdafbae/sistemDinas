<?php

namespace App\Http\Controllers\Verifikasi;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\PerjalananDinas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\LaporanDirevisi;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Notification;
use App\Models\LaporanPerjalananDinas; // Tambahkan ini
use App\Notifications\LaporanDisetujui; // Ubah nama notifikasi jika perlu
use Illuminate\Support\Str; // <-- Pastikan ini ada ATAU gunakan FQCN di bawah


class VerifikasiPerjalananDinasController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:verifikator|superadmin']); // Hanya untuk verifikator
    }

    /**
     * Menampilkan daftar perjalanan dinas yang perlu diverifikasi.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PerjalananDinas::with(['operator', 'personil'])
                ->where('status', 'diproses') // Hanya yang menunggu verifikasi
                ->latest();

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('personil_list', fn($pd) => $pd->personil->pluck('nama')->implode(', '))
                ->addColumn('tanggal_spt_formatted', fn($pd) => Carbon::parse($pd->tanggal_spt)->translatedFormat('d M Y'))
                ->addColumn('tanggal_pelaksanaan', fn($pd) => Carbon::parse($pd->tanggal_mulai)->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($pd->tanggal_selesai)->translatedFormat('d M Y'))
                ->editColumn('total_estimasi_biaya', fn($pd) => 'Rp ' . number_format($pd->total_estimasi_biaya, 0, ',', '.'))
                ->addColumn('action', function ($perjalanan) {
                    $showUrl = route('verifikator.perjalanan-dinas.show', $perjalanan->id);
                    return '<a href="' . $showUrl . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Verifikasi</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('perjalanan_dinas.verifikator.index');
    }

    /**
     * Menampilkan detail perjalanan dinas untuk diverifikasi.
     */
    public function show(PerjalananDinas $perjalananDinas)
    {
        if ($perjalananDinas->status !== 'diproses') {
            return redirect()->route('verifikator.perjalanan-dinas.index')->with('error', 'Pengajuan ini tidak lagi menunggu verifikasi.');
        }
        $perjalananDinas->load(['personil', 'operator', 'biayaDetails.sbuItem', 'biayaDetails.userTerkait']);
        return view('perjalanan_dinas.verifikator.show', compact('perjalananDinas'));
    }

    /**
     * Memproses aksi verifikasi (setuju atau revisi/tolak).
     */
    public function processVerification(Request $request, PerjalananDinas $perjalananDinas)
    {
        if ($perjalananDinas->status !== 'diproses') {
            return redirect()->route('verifikator.perjalanan-dinas.index')->with('error', 'Pengajuan ini tidak lagi menunggu verifikasi.');
        }

        $request->validate([
            'aksi_verifikasi' => 'required|in:setuju,revisi,tolak',
            'catatan_verifikator' => 'nullable|string|required_if:aksi_verifikasi,revisi|required_if:aksi_verifikasi,tolak',
        ]);

        $perjalananDinas->verifikator_id = Auth::id();
        $perjalananDinas->catatan_verifikator = $request->catatan_verifikator;

        if ($request->aksi_verifikasi == 'setuju') {
            $perjalananDinas->status = 'menunggu_persetujuan_atasan';
            // TODO: Kirim notifikasi ke Atasan
            $message = 'Pengajuan berhasil diverifikasi dan diteruskan ke Atasan.';
        } elseif ($request->aksi_verifikasi == 'revisi') {
            $perjalananDinas->status = 'revisi_operator_verifikator';
            // TODO: Kirim notifikasi ke Operator
            $message = 'Pengajuan dikembalikan ke Operator untuk revisi.';
        } elseif ($request->aksi_verifikasi == 'tolak') {
            $perjalananDinas->status = 'ditolak_verifikator';
            // TODO: Kirim notifikasi ke Operator
            $message = 'Pengajuan ditolak oleh Verifikator.';
        }

        $perjalananDinas->save();

        return redirect()->route('verifikator.perjalanan-dinas.index')->with('success', $message);
    }

    public function indexLaporan(Request $request)
    {
        if ($request->ajax()) {
            Log::info('AJAX request received for verifikator.laporan-perjadin.index data.'); // Log Awal

            try {
                $query = LaporanPerjalananDinas::with([
                    'perjalananDinas' => function ($q_pd) {
                        $q_pd->select('id', 'nomor_spt', 'tanggal_spt', 'tujuan_spt'); // Pilih kolom spesifik
                    },
                    'pelapor' => function ($q_user) {
                        $q_user->select('id', 'nama'); // Pilih kolom spesifik
                    }
                ])
                    ->where('status_laporan', 'diserahkan_untuk_verifikasi')
                    ->select('laporan_perjalanan_dinas.*'); // Penting untuk memilih semua kolom dari tabel utama

                Log::info('Query built for DataTables:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]); // Log SQL query
                $dataCount = $query->count(); // Hitung jumlah data sebelum DataTables memprosesnya
                Log::info("Number of records found by query: {$dataCount}");


                return DataTables::eloquent($query)
                    ->addIndexColumn()
                    ->addColumn('nomor_spt', fn($laporan) => $laporan->perjalananDinas->nomor_spt ?? '<em class="text-muted">Belum Ada</em>')
                    ->addColumn('tanggal_spt', fn($laporan) => $laporan->perjalananDinas->tanggal_spt ? Carbon::parse($laporan->perjalananDinas->tanggal_spt)->translatedFormat('d M Y') : '-')
                    ->addColumn('tujuan_spt', fn($laporan) => Str::limit($laporan->perjalananDinas->tujuan_spt ?? '-', 30))
                    ->addColumn('pelapor_nama', fn($laporan) => $laporan->pelapor->nama ?? '-')
                    ->addColumn('tanggal_laporan_formatted', fn($laporan) => $laporan->tanggal_laporan ? Carbon::parse($laporan->tanggal_laporan)->translatedFormat('d M Y') : '-')
                    ->editColumn('total_biaya_rill_dilaporkan', fn($laporan) => 'Rp ' . number_format($laporan->total_biaya_rill_dilaporkan, 0, ',', '.'))
                    ->addColumn('action', function ($laporan) {
                        $showUrl = route('verifikator.laporan-perjadin.show', $laporan->id);
                        return '<a href="' . $showUrl . '" class="btn btn-sm btn-info"><i class="fas fa-search-dollar"></i> Verifikasi Laporan</a>';
                    })
                    ->rawColumns(['action', 'nomor_spt'])
                    ->make(true);
            } catch (\Exception $e) {
                Log::error('Error in VerifikasiLaporanPerjadinController@indexLaporan (AJAX): ' . $e->getMessage() . "\n" . $e->getTraceAsString());
                // Mengembalikan response error JSON jika terjadi exception
                return response()->json(['error' => 'Tidak dapat memuat data laporan.'], 500);
            }
        }
        // Jika request bukan AJAX, tampilkan view (ini sudah benar di method index Anda)
        // Untuk halaman index DataTables, method yang dipanggil oleh ajax adalah yang utama.
        // Pastikan view yang dirender adalah yang benar (verifikator.laporan_index) jika request non-ajax
        if (!$request->ajax()) {
            return view('perjalanan_dinas.verifikator.laporan_index');
        }
    }

    public function showLaporan(LaporanPerjalananDinas $laporan) // Route model binding untuk LaporanPerjalananDinas
    {
        if ($laporan->status_laporan !== 'diserahkan_untuk_verifikasi') {
            return redirect()->route('verifikator.laporan-perjadin.index')->with('error', 'Laporan ini tidak lagi menunggu verifikasi.');
        }
        $laporan->load(['perjalananDinas.personil', 'perjalananDinas.operator', 'pelapor', 'rincianBiaya.sbuItem']);
        $estimasiBiaya = $laporan->perjalananDinas->biayaDetails()->with('sbuItem')->get();

        return view('perjalanan_dinas.verifikator.laporan_show', compact('laporan', 'estimasiBiaya')); // View baru
    }

    /**
     * Memproses aksi verifikasi laporan oleh Verifikator.
     */
    public function processLaporan(Request $request, LaporanPerjalananDinas $laporan)
    {
        if ($laporan->status_laporan !== 'diserahkan_untuk_verifikasi') {
            return redirect()->route('verifikator.laporan-perjadin.index')->with('error', 'Laporan ini tidak lagi menunggu verifikasi.');
        }

        $request->validate([
            'aksi_verifikasi_laporan' => 'required|in:setuju,revisi', // Verifikator mungkin tidak bisa menolak langsung, hanya revisi atau setuju
            'catatan_pereview' => 'nullable|string|required_if:aksi_verifikasi_laporan,revisi|max:1000',
            // Jika verifikator juga menentukan biaya riil final:
            // 'total_biaya_rill_disetujui' => 'required_if:aksi_verifikasi_laporan,setuju|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $laporan->catatan_pereview = $request->catatan_pereview; // Catatan dari verifikator
            $message = '';

            if ($request->aksi_verifikasi_laporan == 'setuju') {
                $laporan->status_laporan = 'selesai_diproses'; // Atau 'disetujui_verifikator' jika ada tahap approval lain
                // Update total biaya riil di PerjalananDinas utama
                $perjalananDinas = $laporan->perjalananDinas;
                // Jika ada input total_biaya_rill_disetujui dari form verifikator, gunakan itu.
                // Jika tidak, gunakan yang dilaporkan pegawai.
                $biayaRillFinal = $request->input('total_biaya_rill_disetujui', $laporan->total_biaya_rill_dilaporkan);
                $perjalananDinas->total_biaya_rill = $biayaRillFinal;
                $perjalananDinas->save();

                $message = 'Laporan perjalanan dinas berhasil diverifikasi dan disetujui.';
                // Kirim notifikasi ke pelapor (pegawai) dan mungkin operator/atasan
                if ($laporan->pelapor) {
                    // $laporan->pelapor->notify(new LaporanDisetujui($laporan, Auth::user())); // Notifikasi LaporanDisetujui
                    Log::info("Notifikasi Laporan Disetujui Verifikator akan dikirim ke user ID: {$laporan->pelapor->id}");
                }
            } elseif ($request->aksi_verifikasi_laporan == 'revisi') {
                $laporan->status_laporan = 'revisi_laporan';
                $message = 'Laporan perjalanan dinas dikembalikan ke pegawai untuk revisi.';
                if ($laporan->pelapor) {
                    // $laporan->pelapor->notify(new LaporanDirevisi($laporan, Auth::user()));
                    Log::info("Notifikasi Laporan Direvisi oleh Verifikator akan dikirim ke user ID: {$laporan->pelapor->id}");
                }
            }
            $laporan->save();
            DB::commit();
            return redirect()->route('verifikator.laporan-perjadin.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saat proses verifikasi laporan oleh Verifikator: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Gagal memproses verifikasi laporan: ' . $e->getMessage());
        }
    }
}
