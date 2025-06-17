<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PerjalananDinas;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Models\LaporanPerjalananDinas; // Import jika digunakan di action button

class MonitoringPerjalananDinasController extends Controller
{
    public function __construct()
    {
        // Middleware ini akan berlaku untuk semua method di controller ini
        $this->middleware(['auth', 'role:atasan|kepala dinas|superadmin']);
    }

    /**
     * Menampilkan halaman monitoring semua perjalanan dinas.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PerjalananDinas::with([
                            'operator:id,nama', // Hanya pilih kolom yang dibutuhkan
                            'personil:users.id,users.nama', // Spesifikasikan nama tabel untuk personil jika ada ambiguitas
                            'verifikator:id,nama',
                            'atasan:id,nama'
                        ])
                        ->select('perjalanan_dinas.*') // Pastikan semua kolom dari tabel utama terpilih
                        ->latest('perjalanan_dinas.created_at');

            // Filter berdasarkan Status
            if ($request->filled('status_filter') && $request->status_filter !== 'semua') {
                $query->where('perjalanan_dinas.status', $request->status_filter);
            }

            // Filter berdasarkan Jenis SPT
            if ($request->filled('jenis_spt_filter') && $request->jenis_spt_filter !== 'semua') {
                $query->where('perjalanan_dinas.jenis_spt', $request->jenis_spt_filter);
            }

            // Filter berdasarkan Rentang Tanggal Pengajuan (Tanggal SPT)
            if ($request->filled('tanggal_spt_mulai')) {
                try {
                    $query->whereDate('perjalanan_dinas.tanggal_spt', '>=', Carbon::parse($request->tanggal_spt_mulai)->format('Y-m-d'));
                } catch (\Exception $e) {
                    Log::warning('Format tanggal_spt_mulai tidak valid: ' . $request->tanggal_spt_mulai);
                }
            }
            if ($request->filled('tanggal_spt_selesai')) {
                 try {
                    $query->whereDate('perjalanan_dinas.tanggal_spt', '<=', Carbon::parse($request->tanggal_spt_selesai)->format('Y-m-d'));
                } catch (\Exception $e) {
                    Log::warning('Format tanggal_spt_selesai tidak valid: ' . $request->tanggal_spt_selesai);
                }
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('nomor_spt_display', fn($pd) => $pd->nomor_spt ?? '<em class="text-muted fst-italic">Belum Terbit</em>')
                ->addColumn('tanggal_spt_formatted', fn($pd) => $pd->tanggal_spt ? Carbon::parse($pd->tanggal_spt)->translatedFormat('d M Y') : '-')
                ->addColumn('operator_nama', fn($pd) => $pd->operator->nama ?? '-')
                ->addColumn('personil_list', fn($pd) => Str::limit($pd->personil->pluck('nama')->implode(', '), 35))
                ->addColumn('tujuan_display', function($pd){
                    $tujuan = Str::limit($pd->tujuan_spt, 25);
                    if ($pd->kota_tujuan_id) $tujuan .= ($tujuan ? ', ' : '') . Str::limit($pd->kota_tujuan_id,15);
                    if ($pd->provinsi_tujuan_id) $tujuan .= ($tujuan ? ', ' : '') . 'Prov. ' . Str::limit($pd->provinsi_tujuan_id,15);
                    return $tujuan ?: '-';
                })
                ->editColumn('jenis_spt_display', fn($pd) => ucwords(str_replace('_', ' ', $pd->jenis_spt)))
                ->editColumn('status', function ($pd) {
                    $statusText = ucwords(str_replace('_', ' ', $pd->status));
                    $badgeClass = 'bg-gradient-secondary';
                    if ($pd->status === 'disetujui' || $pd->status === 'selesai') $badgeClass = 'bg-gradient-success';
                    if (Str::contains($pd->status, 'revisi')) $badgeClass = 'bg-gradient-warning';
                    if (Str::contains($pd->status, 'tolak')) $badgeClass = 'bg-gradient-danger';
                    if ($pd->status === 'diproses' || $pd->status === 'menunggu_persetujuan_atasan') $badgeClass = 'bg-gradient-info';
                    return "<span class='badge {$badgeClass}'>{$statusText}</span>";
                })
                ->addColumn('action', function ($perjalanan) {
                    // Link detail akan mengarah ke view yang sesuai dengan statusnya
                    // Ini adalah contoh sederhana, Anda bisa membuat logika yang lebih kompleks
                    // atau satu halaman detail universal yang menampilkan info berdasarkan role.
                    $detailUrl = '#';
                    if (in_array($perjalanan->status, ['disetujui', 'selesai'])) {
                        // Cek apakah ada laporan
                        $laporanExists = LaporanPerjalananDinas::where('perjalanan_dinas_id', $perjalanan->id)->exists();
                        if ($laporanExists) {
                            $userPelapor = $perjalanan->personil->first(); // Ambil user pertama sebagai contoh pelapor jika ada
                            $detailUrl = route('pegawai.laporan-perjadin.show', ['perjalananDinas' => $perjalanan->id, 'user_laporan_id' => $userPelapor->id ?? 0]);
                        } else {
                             // Link ke daftar download dokumen jika laporan belum ada tapi SPT/SPPD sudah bisa di-generate
                            $detailUrl = route('dokumen.index'); // Atau ke detail pengajuan operator
                        }
                    } elseif ($perjalanan->status == 'menunggu_persetujuan_atasan') {
                        $detailUrl = route('atasan.perjalanan-dinas.show', $perjalanan->id);
                    } elseif ($perjalanan->status == 'diproses') {
                        $detailUrl = route('verifikator.perjalanan-dinas.show', $perjalanan->id);
                    } else { // Draft, revisi, dll. -> arahkan ke detail operator
                        $detailUrl = route('operator.perjalanan-dinas.show', $perjalanan->id);
                    }
                    return '<a href="' . $detailUrl . '" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Lihat Detail"><i class="fas fa-eye"></i></a>';
                })
                ->rawColumns(['action', 'status', 'nomor_spt_display', 'tujuan_display'])
                ->filterColumn('operator_nama', function($query, $keyword) {
                    $query->whereHas('operator', function($q) use ($keyword) {
                        $q->where('nama', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('personil_list', function($query, $keyword) { // Pencarian sederhana pada nama personil
                    $query->whereHas('personil', function($q) use ($keyword) {
                        $q->where('users.nama', 'like', "%{$keyword}%"); // Pastikan prefix tabel users.
                    });
                })
                ->make(true);
        }

        $allStatus = [
            'draft' => 'Draft', 'diproses' => 'Diproses (Menunggu Verifikator)',
            'revisi_operator_verifikator' => 'Revisi dari Verifikator',
            'menunggu_persetujuan_atasan' => 'Menunggu Persetujuan Atasan',
            'revisi_operator_atasan' => 'Revisi dari Atasan', 'disetujui' => 'Disetujui Atasan',
            'selesai' => 'Selesai (Dokumen Terbit)', 'ditolak_verifikator' => 'Ditolak Verifikator',
            'ditolak_atasan' => 'Ditolak Atasan', 'dibatalkan' => 'Dibatalkan Operator'
        ];
        $jenisSPTList = [
            'dalam_daerah' => 'Dalam Daerah',
            'luar_daerah_dalam_provinsi' => 'Luar Daerah (Dalam Provinsi)',
            'luar_daerah_luar_provinsi' => 'Luar Daerah (Luar Provinsi)',
        ];

        return view('admin.monitoring.perjalanan_dinas_index', compact('allStatus', 'jenisSPTList'));
    }
}