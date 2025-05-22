<?php

namespace App\Http\Controllers\Persetujuan; // Sesuaikan namespace

use App\Http\Controllers\Controller;
use App\Models\PerjalananDinas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class PersetujuanPerjalananDinasController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:atasan']); // Hanya untuk Atasan
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
        if ($perjalananDinas->status !== 'menunggu_persetujuan_atasan') {
            return redirect()->route('atasan.perjalanan-dinas.index')->with('error', 'Pengajuan ini tidak lagi menunggu persetujuan Anda.');
        }
        // Otorisasi tambahan jika perlu (misal, atasan hanya untuk OPD tertentu)
        // $this->authorize('approve', $perjalananDinas); // Asumsi ada Policy

        $perjalananDinas->load(['personil', 'operator', 'verifikator', 'biayaDetails.sbuItem', 'biayaDetails.userTerkait']);
        return view('perjalanan_dinas.atasan.show', compact('perjalananDinas'));
    }

    /**
     * Memproses aksi persetujuan (setuju atau revisi/tolak).
     */
    public function processApproval(Request $request, PerjalananDinas $perjalananDinas)
    {
        if ($perjalananDinas->status !== 'menunggu_persetujuan_atasan') {
            return redirect()->route('atasan.perjalanan-dinas.index')->with('error', 'Pengajuan ini tidak lagi menunggu persetujuan Anda.');
        }
        // $this->authorize('approve', $perjalananDinas);

        $request->validate([
            'aksi_persetujuan' => 'required|in:setuju,revisi,tolak',
            'catatan_atasan' => 'nullable|string|required_if:aksi_persetujuan,revisi|required_if:aksi_persetujuan,tolak',
        ]);

        $perjalananDinas->atasan_id = Auth::id();
        $perjalananDinas->catatan_atasan = $request->catatan_atasan;

        if ($request->aksi_persetujuan == 'setuju') {
            $perjalananDinas->status = 'selesai'; // Langsung 'selesai' jika atasan adalah final approver
            // Atau 'disetujui' jika ada proses lain setelah ini (misal, bagian keuangan)
            // TODO: Kirim notifikasi ke Operator dan Personil yang berangkat
            $message = 'Pengajuan perjalanan dinas berhasil disetujui.';
        } elseif ($request->aksi_persetujuan == 'revisi') {
            $perjalananDinas->status = 'revisi_operator_atasan';
            // TODO: Kirim notifikasi ke Operator
            $message = 'Pengajuan dikembalikan ke Operator untuk revisi dari Atasan.';
        } elseif ($request->aksi_persetujuan == 'tolak') {
            $perjalananDinas->status = 'ditolak_atasan';
            // TODO: Kirim notifikasi ke Operator
            $message = 'Pengajuan ditolak oleh Atasan.';
        }

        $perjalananDinas->save();

        return redirect()->route('atasan.perjalanan-dinas.index')->with('success', $message);
    }
}