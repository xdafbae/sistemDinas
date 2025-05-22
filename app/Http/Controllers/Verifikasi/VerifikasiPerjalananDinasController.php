<?php

namespace App\Http\Controllers\Verifikasi; // Sesuaikan namespace

use App\Http\Controllers\Controller;
use App\Models\PerjalananDinas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class VerifikasiPerjalananDinasController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:verifikator']); // Hanya untuk verifikator
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
}