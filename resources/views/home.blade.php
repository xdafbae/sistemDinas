@extends('layouts.app') {{-- Pastikan layout ini sudah dikonfigurasi untuk Argon --}}

@section('title', 'Dashboard - ' . config('app.name', 'Laravel'))

@section('page_name', 'Dashboard') {{-- Untuk breadcrumb atau header di layout --}}

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Pegawai</p>
                                <h5 class="font-weight-bolder">
                                    {{ $totalPegawai ?? 0 }}
                                </h5>
                                <p class="mb-0">
                                    <span class="text-success text-sm font-weight-bolder">+{{ $pegawaiBaruHariIni ?? 0 }}</span>
                                    hari ini
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                <i class="ni ni-circle-08 text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Role</p>
                                <h5 class="font-weight-bolder">
                                    {{ $totalRole ?? 0 }}
                                </h5>
                                <p class="mb-0">
                                    <span class="text-success text-sm font-weight-bolder"></span>
                                     Terdaftar
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                <i class="ni ni-badge text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">User Aktif (7 hari)</p>
                                <h5 class="font-weight-bolder">
                                    {{ $userAktifEstimasi ?? 0 }}
                                </h5>
                                <p class="mb-0">
                                    <span class="text-info text-sm font-weight-bolder">Estimasi</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                <i class="ni ni-user-run text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Login Hari Ini</p>
                                <h5 class="font-weight-bolder">
                                    {{-- Data ini perlu sistem logging login, untuk contoh 0 --}}
                                    0
                                </h5>
                                <p class="mb-0">
                                    <span class="text-sm font-weight-bolder">Pengguna</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                <i class="ni ni-key-25 text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-7 mb-lg-0 mb-4">
            <div class="card z-index-2 h-100">
                <div class="card-header pb-0 pt-3 bg-transparent">
                    <h6 class="text-capitalize">Statistik Pertumbuhan Pegawai (12 Bulan Terakhir)</h6>
                    {{-- <p class="text-sm mb-0">
                        <i class="fa fa-arrow-up text-success"></i>
                        <span class="font-weight-bold">Analisis</span> Tren
                    </p> --}}
                </div>
                <div class="card-body p-3">
                    <div class="chart">
                        <canvas id="chart-line" class="chart-canvas" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card card-carousel overflow-hidden h-100 p-0">
                <div id="carouselExampleCaptions" class="carousel slide h-100" data-bs-ride="carousel">
                    <div class="carousel-inner border-radius-lg h-100">
                        <div class="carousel-item h-100 active" style="background-image: url('{{ asset('assets/img/carousel-1.jpg') }}'); background-size: cover;">
                            <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                                <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                                    <i class="ni ni-bell-55 text-dark opacity-10"></i>
                                </div>
                                <h5 class="text-white mb-1">Selamat Datang, {{ Auth::user()->nama ?? 'Pengguna' }}!</h5>
                                <p>Ini adalah dashboard sistem kepegawaian Anda.</p>
                            </div>
                        </div>
                        <div class="carousel-item h-100" style="background-image: url('{{ asset('assets/img/carousel-2.jpg') }}'); background-size: cover;">
                            <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                                <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                                    <i class="ni ni-bulb-61 text-dark opacity-10"></i>
                                </div>
                                <h5 class="text-white mb-1">Pengumuman Penting</h5>
                                <p>Pastikan untuk selalu mengecek update terbaru dari admin.</p>
                            </div>
                        </div>
                        <div class="carousel-item h-100" style="background-image: url('{{ asset('assets/img/carousel-3.jpg') }}'); background-size: cover;">
                            <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                                <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                                    <i class="ni ni-calendar-grid-58 text-dark opacity-10"></i>
                                </div>
                                <h5 class="text-white mb-1">Agenda & Jadwal</h5>
                                <p>Periksa agenda kegiatan dan jadwal penting lainnya.</p>
                            </div>
                        </div>
                    </div>
                    <button class="carousel-control-prev w-5 me-3" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next w-5 me-3" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-12 mb-lg-0 mb-4">
            <div class="card">
                <div class="card-header pb-0 p-3">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-2">Pegawai Terbaru (5 Teratas)</h6>
                        @can('manage users') {{-- Ganti 'manage users' dengan permission yang sesuai --}}
                            <a href="{{-- route('users.index') --}}" class="btn btn-sm btn-primary mb-0">Lihat Semua Pegawai</a>
                        @endcan
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pegawai</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Jabatan & Role</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Golongan</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl. Bergabung</th>
                                <th class="text-secondary opacity-7"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($latestUsers ?? [] as $user)
                            <tr>
                                <td>
                                    <div class="d-flex px-2 py-1">
                                        <div>
                                            {{-- Ganti dengan path avatar dinamis jika ada --}}
                                            <img src="{{ asset('assets/img/default-avatar.png') }}" class="avatar avatar-sm me-3" alt="user_image">
                                        </div>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">{{ $user->nama }}</h6>
                                            <p class="text-xs text-secondary mb-0">{{ $user->email }}</p>
                                            @if($user->nip)
                                            <p class="text-xs text-secondary mb-0">NIP: {{ $user->nip }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0">{{ $user->jabatan ?? '-' }}</p>
                                    <p class="text-xs text-secondary mb-0">{{ $user->getRoleNames()->join(', ') ?: 'Belum ada role' }}</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                    <span class="badge badge-sm bg-gradient-info">{{ $user->gol ?? '-' }}</span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</span>
                                </td>
                                <td class="align-middle">
                                    @can('edit users') {{-- Ganti dengan permission yang sesuai --}}
                                    <a href="{{-- route('users.edit', $user->id) --}}" class="text-secondary font-weight-bold text-xs" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Pegawai">
                                        Edit
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-3">
                                    <p class="mb-0 text-sm">Belum ada data pegawai terbaru.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer bisa diletakkan di layouts.app.blade.php --}}
</div>
@endsection

@push('scripts')
{{-- Pastikan Chart.js sudah di-include di layout utama atau di sini --}}
{{-- Jika belum: <script src="{{ asset('assets/js/plugins/chartjs.min.js') }}"></script> --}}
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var ctx1 = document.getElementById("chart-line").getContext("2d");

    var gradientStroke1 = ctx1.createLinearGradient(0, 230, 0, 50);
    gradientStroke1.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
    gradientStroke1.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
    gradientStroke1.addColorStop(0, 'rgba(94, 114, 228, 0)');

    // Ambil data dari variabel PHP yang di-pass controller
    var chartLabels = @json($chartLabels ?? []);
    var chartData = @json($chartData ?? []);

    if (chartLabels.length > 0 && chartData.length > 0) {
      new Chart(ctx1, {
        type: "line",
        data: {
          labels: chartLabels,
          datasets: [{
            label: "Pegawai Baru",
            tension: 0.4,
            borderWidth: 0,
            pointRadius: 0,
            borderColor: "#5e72e4",
            backgroundColor: gradientStroke1,
            borderWidth: 3,
            fill: true,
            data: chartData,
            maxBarThickness: 6
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false,
            }
          },
          interaction: {
            intersect: false,
            mode: 'index',
          },
          scales: {
            y: {
              grid: {
                drawBorder: false,
                display: true,
                drawOnChartArea: true,
                drawTicks: false,
                borderDash: [5, 5],
                color: 'rgba(0, 0, 0, .08)' // Warna grid sumbu Y
              },
              ticks: {
                display: true,
                padding: 10,
                color: '#6c757d', // Warna teks ticks sumbu Y
                font: {
                  size: 11,
                  family: "Open Sans",
                  style: 'normal',
                  lineHeight: 2
                },
              }
            },
            x: {
              grid: {
                drawBorder: false,
                display: false, // Biasanya grid X tidak ditampilkan untuk line chart
                drawOnChartArea: false,
                drawTicks: false,
                borderDash: [5, 5]
              },
              ticks: {
                display: true,
                color: '#6c757d', // Warna teks ticks sumbu X
                padding: 10, // Ubah padding jika perlu
                font: {
                  size: 11,
                  family: "Open Sans",
                  style: 'normal',
                  lineHeight: 2
                },
              }
            },
          },
        },
      });
    } else {
        // Tampilkan pesan jika tidak ada data chart
        var chartContainer = document.getElementById("chart-line").parentElement;
        if(chartContainer) {
            chartContainer.innerHTML = "<p class='text-center p-5 text-muted'>Tidak ada data statistik pertumbuhan pegawai untuk ditampilkan.</p>";
        }
    }
  });
</script>
@endpush