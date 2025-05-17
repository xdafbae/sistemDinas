<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Pastikan User model Anda di sini
use Spatie\Permission\Models\Role; // Untuk Spatie Roles
use Illuminate\Support\Carbon; // Untuk manipulasi tanggal

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth'); // Memastikan hanya user terautentikasi yang bisa akses
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Data untuk Kartu Statistik
        $totalPegawai = User::count();
        $pegawaiBaruHariIni = User::whereDate('created_at', today())->count();
        $totalRole = Role::count();
        // Contoh data user aktif (misal: login dalam 7 hari terakhir, memerlukan field last_login_at atau activity log)
        // Untuk contoh ini, kita hardcode saja atau gunakan data lain yang relevan
        $userAktifEstimasi = User::where('updated_at', '>=', Carbon::now()->subDays(7))->count(); // Contoh kasar

        // Data untuk Tabel Pegawai Terbaru
        $latestUsers = User::latest()->take(5)->get();

        // Data untuk Chart Pertumbuhan Pegawai
        $chartLabels = [];
        $chartData = [];

        // Ambil data pertumbuhan pegawai per bulan selama 12 bulan terakhir
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Query untuk mengambil jumlah user baru per bulan
        $pegawaiPerBulan = User::selectRaw("DATE_FORMAT(created_at, '%b %Y') as bulan_tahun_label, COUNT(*) as jumlah")
                                ->whereBetween('created_at', [$startDate, $endDate])
                                ->groupBy('bulan_tahun_label')
                                ->orderByRaw("MIN(created_at)") // Urutkan berdasarkan tanggal aktual
                                ->get()
                                ->keyBy('bulan_tahun_label'); // Gunakan 'Bln Tahun' sebagai key untuk memudahkan lookup

        // Inisialisasi array untuk 12 bulan terakhir dengan data 0
        // dan isi dengan data dari query jika ada
        $currentMonthIterator = $startDate->copy();
        while ($currentMonthIterator <= $endDate) {
            $labelBulanTahun = $currentMonthIterator->format('M Y'); // Format label: Jan 2023
            $chartLabels[] = $labelBulanTahun;
            $chartData[] = $pegawaiPerBulan->has($labelBulanTahun) ? $pegawaiPerBulan[$labelBulanTahun]->jumlah : 0;
            $currentMonthIterator->addMonth();
        }

        return view('home', compact(
            'totalPegawai',
            'pegawaiBaruHariIni',
            'totalRole',
            'userAktifEstimasi',
            'latestUsers',
            'chartLabels',
            'chartData'
        ));
    }
}