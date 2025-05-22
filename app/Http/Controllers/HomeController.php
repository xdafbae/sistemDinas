<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PerjalananDinas;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Untuk query yang lebih kompleks jika perlu

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $today = Carbon::now()->toDateString();

        // --- Data untuk Card Stats Baru ---
        $totalPerjalananBulanIni = PerjalananDinas::whereYear('tanggal_spt', $currentYear)
            ->whereMonth('tanggal_spt', $currentMonth)
            ->where('status', 'selesai') // Kondisi status 'selesai'
            ->count();

        $totalEstimasiBiayaBulanIni = PerjalananDinas::whereYear('tanggal_spt', $currentYear)
            ->whereMonth('tanggal_spt', $currentMonth)
            ->sum('total_estimasi_biaya');

        // Menghitung jumlah daerah tujuan unik bulan ini.
        // Kita bisa menggunakan provinsi_tujuan_id atau kota_tujuan_id tergantung detail yang diinginkan.
        // Di sini saya gunakan provinsi_tujuan_id.
        $jumlahDaerahTujuanUnikBulanIni = PerjalananDinas::whereYear('tanggal_spt', $currentYear)
            ->whereMonth('tanggal_spt', $currentMonth)
            ->whereNotNull('provinsi_tujuan_id')
            ->where('provinsi_tujuan_id', '!=', '')
            ->distinct('provinsi_tujuan_id')
            ->count('provinsi_tujuan_id');

        // Jumlah pengajuan yang menunggu tindakan (verifikasi atau persetujuan atasan)
        $pengajuanMenungguTindakan = PerjalananDinas::whereIn('status', ['diproses', 'menunggu_persetujuan_atasan'])
            ->count();


        // --- Data untuk Chart: Statistik Perjalanan Dinas per Daerah Tujuan (Bulan Ini) ---
        // Kita akan mengambil 5 daerah tujuan teratas sebagai contoh
        $perjalananPerDaerah = PerjalananDinas::whereYear('tanggal_spt', $currentYear)
            ->whereMonth('tanggal_spt', $currentMonth)
            ->whereNotNull('provinsi_tujuan_id') // Pastikan ada provinsi tujuan
            ->where('provinsi_tujuan_id', '!=', '')
            ->select('provinsi_tujuan_id', DB::raw('count(*) as total_perjalanan'))
            ->groupBy('provinsi_tujuan_id')
            ->orderBy('total_perjalanan', 'desc')
            ->take(5) // Ambil 5 teratas
            ->get();

        $chartDaerahLabels = $perjalananPerDaerah->pluck('provinsi_tujuan_id')->toArray();
        $chartDaerahData = $perjalananPerDaerah->pluck('total_perjalanan')->toArray();


        // --- Data untuk Tabel Pengajuan Perjalanan Dinas Terbaru (masih relevan) ---
        $latestPerjalananDinas = PerjalananDinas::with('personil')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        // 5. Total Perjalanan Dinas SELESAI Hari Ini
        // Untuk saat ini, saya biarkan ini menghitung semua perjalanan pada hari ini.
        // Jika ingin hanya yang selesai, tambahkan ->where('status', 'selesai')
        $totalPerjalananHariIni = PerjalananDinas::whereDate('tanggal_spt', $today)
            ->where('status', 'selesai') // Kondisi status 'selesai' ditambahkan
            ->count();

        return view('home', compact(
            'totalPerjalananBulanIni',
            'totalEstimasiBiayaBulanIni',
            'jumlahDaerahTujuanUnikBulanIni',
            'pengajuanMenungguTindakan',
            'chartDaerahLabels',
            'chartDaerahData',
            'latestPerjalananDinas',
            'totalPerjalananHariIni'
        ));
    }
}
