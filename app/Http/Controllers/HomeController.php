<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\PerjalananDinas;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user(); // Dapatkan user yang sedang login
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $today = Carbon::now()->toDateString();


        // --- Tentukan apakah user adalah admin/pengelola yang melihat semua data ---
        $canViewAllData = $user->hasAnyRole(['verifikator', 'atasan', 'kepala dinas', 'superadmin']);

        // --- Data untuk Card Stats ---

        // 1. Total Perjalanan Bulan Ini
        $totalPerjalananQuery = PerjalananDinas::whereYear('tanggal_spt', $currentYear)
            ->whereMonth('tanggal_spt', $currentMonth);
        if (!$canViewAllData) {
            // Jika bukan admin/pengelola, filter berdasarkan perjalanan yang melibatkan user ini sebagai personil
            // atau jika dia operator, perjalanan yang dia buat
            $totalPerjalananQuery->where(function ($query) use ($user) {
                $query->where('operator_id', $user->id) // Perjalanan yang diajukan olehnya (jika operator)
                    ->orWhereHas('personil', function ($q) use ($user) {
                        $q->where('users.id', $user->id); // Perjalanan yang dia ikuti
                    });
            });
        }
        $totalPerjalananBulanIni = $totalPerjalananQuery->count();

        // 2. Total Estimasi Biaya Perjalanan Dinas Bulan Ini
        $totalEstimasiBiayaQuery = PerjalananDinas::whereYear('tanggal_spt', $currentYear)
            ->whereMonth('tanggal_spt', $currentMonth);
        if (!$canViewAllData) {
            $totalEstimasiBiayaQuery->where(function ($query) use ($user) {
                $query->where('operator_id', $user->id)
                    ->orWhereHas('personil', function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    });
            });
        }
        $totalEstimasiBiayaBulanIni = $totalEstimasiBiayaQuery->sum('total_estimasi_biaya');

        // 3. Jumlah Daerah (Provinsi) Tujuan Unik Bulan Ini
        $daerahTujuanQuery = PerjalananDinas::whereYear('tanggal_spt', $currentYear)
            ->whereMonth('tanggal_spt', $currentMonth)
            ->whereNotNull('provinsi_tujuan_id')
            ->where('provinsi_tujuan_id', '!=', '');
        if (!$canViewAllData) {
            $daerahTujuanQuery->where(function ($query) use ($user) {
                $query->where('operator_id', $user->id)
                    ->orWhereHas('personil', function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    });
            });
        }
        $jumlahDaerahTujuanUnikBulanIni = $daerahTujuanQuery->distinct('provinsi_tujuan_id')->count('provinsi_tujuan_id');

        // 4. Pengajuan Menunggu Tindakan (Verifikasi atau Persetujuan Atasan)
        // Kartu ini mungkin lebih relevan untuk admin/pengelola.
        // Untuk user biasa, ini bisa 0 atau menampilkan status pengajuan mereka.
        // Untuk sekarang, kita tampilkan data global jika admin, atau 0 jika user biasa (atau Anda bisa kustomisasi)
        $pengajuanMenungguTindakan = 0;
        if ($canViewAllData) {
            $pengajuanMenungguTindakan = PerjalananDinas::whereIn('status', ['diproses', 'menunggu_persetujuan_atasan'])->count();
        } else {
            // Jika user biasa, mungkin tampilkan pengajuan miliknya yang sedang diproses/menunggu persetujuan atasan
            $pengajuanMenungguTindakan = PerjalananDinas::whereIn('status', ['diproses', 'menunggu_persetujuan_atasan'])
                ->where(function ($query) use ($user) {
                    $query->where('operator_id', $user->id)
                        ->orWhereHas('personil', function ($q) use ($user) {
                            $q->where('users.id', $user->id);
                        });
                })
                ->count();
        }

        // --- Data untuk User Perjalanan Dinas Card ---
        // Jika admin, ambil data perjalanan dinas untuk semua user
        // Jika user biasa, hanya ambil data perjalanan dinas miliknya
        $userPerjalananDinas = [];
        
        if ($canViewAllData) {
            // Untuk admin: ambil data perjalanan dinas per user (top 5)
            $userPerjalananDinas = DB::table('users')
                ->leftJoin('perjalanan_dinas_personil', 'users.id', '=', 'perjalanan_dinas_personil.user_id')
                ->leftJoin('perjalanan_dinas', 'perjalanan_dinas_personil.perjalanan_dinas_id', '=', 'perjalanan_dinas.id')
                ->select('users.id', 'users.nama', 'users.nip', DB::raw('count(perjalanan_dinas.id) as total_perjalanan'))
                ->where('users.aktif', true)
                ->groupBy('users.id', 'users.nama', 'users.nip')
                ->orderBy('total_perjalanan', 'desc')
                ->take(5)
                ->get();
        } else {
            // Untuk user biasa: hitung total perjalanan dinas miliknya
            $totalPerjalananUser = PerjalananDinas::whereHas('personil', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })->count();
            
            // Data untuk status perjalanan dinas user
            $perjalananDiproses = PerjalananDinas::whereIn('status', ['diproses', 'menunggu_persetujuan_atasan'])
                ->whereHas('personil', function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                })->count();
                
            $perjalananDisetujui = PerjalananDinas::whereIn('status', ['disetujui', 'selesai'])
                ->whereHas('personil', function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                })->count();
                
            $perjalananDitolak = PerjalananDinas::whereIn('status', ['ditolak', 'dibatalkan'])
                ->whereHas('personil', function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                })->count();
                
            $userPerjalananDinas = [
                'total' => $totalPerjalananUser,
                'diproses' => $perjalananDiproses,
                'disetujui' => $perjalananDisetujui,
                'ditolak' => $perjalananDitolak
            ];
        }

        // --- Data untuk Chart: Top 5 Daerah Tujuan Perjalanan Dinas (Bulan Ini) ---
        // Chart juga akan disesuaikan berdasarkan hak akses
        $chartDaerahQuery = PerjalananDinas::whereYear('tanggal_spt', $currentYear)
            ->whereMonth('tanggal_spt', $currentMonth)
            ->whereNotNull('provinsi_tujuan_id')
            ->where('provinsi_tujuan_id', '!=', '');

        if (!$canViewAllData) {
            $chartDaerahQuery->where(function ($query) use ($user) {
                $query->where('operator_id', $user->id)
                    ->orWhereHas('personil', function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    });
            });
        }

        $topDaerahTujuan = $chartDaerahQuery->select('provinsi_tujuan_id', DB::raw('count(*) as total_perjalanan'))
            ->groupBy('provinsi_tujuan_id')
            ->orderBy('total_perjalanan', 'desc')
            ->take(5)
            ->get();

        $chartTopDaerahLabels = $topDaerahTujuan->pluck('provinsi_tujuan_id')->toArray();
        $chartTopDaerahData = $topDaerahTujuan->pluck('total_perjalanan')->toArray();


        // --- Data untuk Tabel Pengajuan Perjalanan Dinas Terbaru (5 Teratas) ---
        // Tabel ini juga akan difilter berdasarkan hak akses
        $latestPerjalananDinasQuery = PerjalananDinas::with(['personil' => function ($query) {
            $query->select('users.id', 'users.nama', 'users.nip');
        }])
            ->orderBy('created_at', 'desc');
        if (!$canViewAllData) {
            $latestPerjalananDinasQuery->where(function ($query) use ($user) {
                $query->where('operator_id', $user->id)
                    ->orWhereHas('personil', function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    });
            });
        }
        $latestPerjalananDinas = $latestPerjalananDinasQuery->take(5)->get();

        $totalPerjalananHariIni = PerjalananDinas::whereDate('tanggal_spt', $today)
            ->where('status', 'selesai') // Kondisi status 'selesai' ditambahkan
            ->count();

        return view('home', compact(
            'totalPerjalananBulanIni',
            'totalEstimasiBiayaBulanIni',
            'jumlahDaerahTujuanUnikBulanIni',
            'pengajuanMenungguTindakan',
            'chartTopDaerahLabels',
            'chartTopDaerahData',
            'latestPerjalananDinas',
            'canViewAllData', // Kirim flag ini ke view jika perlu untuk UI berbeda
            'userPerjalananDinas', // Data untuk card perjalanan dinas per user
            'totalPerjalananHariIni'
        ));
    }
}
