@extends('layouts.app') {{-- Pastikan layout ini sudah dikonfigurasi untuk Argon Dashboard 2 --}}

@section('title', 'Dashboard - ' . config('app.name', 'Laravel'))
@section('page_name', 'Dashboard')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            {{-- Card: Perjalanan Bulan Ini & Hari Ini --}}
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Perjalanan Bulan Ini</p>
                                    <h5 class="font-weight-bolder">
                                        {{ $totalPerjalananBulanIni ?? 0 }}
                                    </h5>
                                    <p class="mb-0">
                                        <span
                                            class="text-success text-sm font-weight-bolder">+{{ $totalPerjalananHariIni ?? 0 }}</span>
                                        hari ini
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                    <i class="ni ni-calendar-grid-58 text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card: Estimasi Biaya Bulan Ini --}}
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Estimasi Biaya (Bulan)</p>
                                    <h5 class="font-weight-bolder">
                                        Rp {{ number_format($totalEstimasiBiayaBulanIni ?? 0, 0, ',', '.') }}
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-sm text-info font-weight-bolder">Bulan
                                            {{ \Illuminate\Support\Carbon::now()->format('F') }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                    <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card: Jumlah Daerah Tujuan Unik Bulan Ini --}}
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Daerah Tujuan (Unik)</p>
                                    <h5 class="font-weight-bolder">
                                        {{ $jumlahDaerahTujuanUnikBulanIni ?? 0 }} Provinsi
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-sm text-success font-weight-bolder">Bulan Ini</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                    <i class="ni ni-map-big text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card: Pengajuan Menunggu Tindakan --}}
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Menunggu Tindakan</p>
                                    <h5 class="font-weight-bolder">
                                        {{ $pengajuanMenungguTindakan ?? 0 }} Pengajuan
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-sm text-warning font-weight-bolder">Verifikasi/Approval</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                    <i class="ni ni-bell-55 text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            {{-- Chart: Statistik Perjalanan Dinas per Daerah Tujuan (Bulan Ini) --}}
            <div class="col-lg-7 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <h6 class="text-capitalize">Top 5 Daerah Tujuan Perjalanan Dinas (Bulan Ini)</h6>
                        <p class="text-sm mb-0">
                            <i class="fa fa-arrow-up text-success"></i>
                            <span class="font-weight-bold">Berdasarkan jumlah perjalanan</span> ke provinsi tujuan.
                        </p>
                    </div>
                    <div class="card-body p-3">
                        <div class="chart">
                            <canvas id="chart-daerah-tujuan" class="chart-canvas" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Carousel Informasi --}}
            <div class="col-lg-5">
                <div class="card card-carousel overflow-hidden h-100 p-0">
                    <div id="carouselExampleCaptions" class="carousel slide h-100" data-bs-ride="carousel">
                        <div class="carousel-inner border-radius-lg h-100">
                            <div class="carousel-item h-100 active"
                                style="background-image: url('{{ asset('assets/img/carousel-1.jpg') }}'); background-size: cover;">
                                <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                                    <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                                        <i class="ni ni-circle-08 text-dark opacity-10"></i>
                                    </div>
                                    <h5 class="text-white mb-1">Selamat Datang,
                                        {{ Auth::user()->name ?? (Auth::user()->nama ?? 'Pengguna') }}!</h5>
                                    <p>Ini adalah dashboard sistem perjalanan dinas Anda.</p>
                                </div>
                            </div>
                            <div class="carousel-item h-100"
                                style="background-image: url('{{ asset('assets/img/carousel-2.jpg') }}'); background-size: cover;">
                                <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                                    <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                                        <i class="ni ni-trophy text-dark opacity-10"></i>
                                    </div>
                                    <h5 class="text-white mb-1">Manajemen Efisien!</h5>
                                    <p>Kelola semua perjalanan dinas dengan mudah dan transparan.</p>
                                </div>
                            </div>
                            <div class="carousel-item h-100"
                                style="background-image: url('{{ asset('assets/img/carousel-3.jpg') }}'); background-size: cover;">
                                <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                                    <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                                        <i class="ni ni-notification-70 text-dark opacity-10"></i>
                                    </div>
                                    <h5 class="text-white mb-1">Tetap Terinformasi!</h5>
                                    <p>Dapatkan update terbaru mengenai status pengajuan Anda.</p>
                                </div>
                            </div>
                        </div>
                        <button class="carousel-control-prev w-5 me-3" type="button"
                            data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next w-5 me-3" type="button"
                            data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
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
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Pengajuan Perjalanan Dinas Terbaru (5 Teratas)</h6>
                            {{-- Assuming you have a route for all 'perjalanan dinas' --}}
                            @can('view_all_perjalanan_dinas')
                                {{-- Example permission --}}
                                <a href="{{ route('operator.perjalanan-dinas.index') }}"
                                    class="btn btn-sm btn-primary mb-0">Lihat Semua Pengajuan</a>
                            @endcan
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Personil & No. SPT</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Tujuan</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Tanggal SPT</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Status</th>
                                    <th class="text-secondary opacity-7">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestPerjalananDinas as $perjalanan)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div>
                                                    {{-- Assuming personil has an avatar, otherwise use a default --}}
                                                    <img src="" class="avatar avatar-sm me-3" alt="user_image">
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">
                                                        {{ Str::limit($perjalanan->personil->pluck('nama')->implode(', '), 30) }}
                                                    </h6>
                                                    <p class="text-xs text-secondary mb-0">SPT:
                                                        {{ $perjalanan->nomor_spt ?? '-' }}</p>
                                                    <p class="text-xs text-secondary mb-0">NIP:
                                                        {{ $perjalanan->personil->first()->nip ?? '-' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">
                                                {{ $perjalanan->kotaTujuan->nama_kota ?? ($perjalanan->provinsiTujuan->nama_provinsi ?? 'N/A') }}
                                            </p>
                                            <p class="text-xs text-secondary mb-0">
                                                {{ $perjalanan->provinsiTujuan->nama_provinsi ?? '' }}</p>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span class="text-secondary text-xs font-weight-bold">
                                                {{ $perjalanan->tanggal_spt ? \Illuminate\Support\Carbon::parse($perjalanan->tanggal_spt)->format('d M Y') : '-' }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            @php
                                                $statusClass = 'secondary'; // default
                                                if (in_array($perjalanan->status, ['disetujui', 'selesai'])) {
                                                    $statusClass = 'success';
                                                }
                                                if (in_array($perjalanan->status, ['ditolak', 'dibatalkan'])) {
                                                    $statusClass = 'danger';
                                                }
                                                if (
                                                    in_array($perjalanan->status, [
                                                        'diproses',
                                                        'menunggu_persetujuan_atasan',
                                                    ])
                                                ) {
                                                    $statusClass = 'warning';
                                                }
                                            @endphp
                                            <span class="badge badge-sm bg-gradient-{{ $statusClass }}">
                                                {{ Str::title(str_replace('_', ' ', $perjalanan->status ?? '-')) }}
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            {{-- Adjust route as per your application for viewing a 'perjalanan dinas' --}}
                                            <a href="{{ route('operator.perjalanan-dinas.show', $perjalanan->id) }}"
                                                {{-- Example Route --}} class="text-secondary font-weight-bold text-xs"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Detail">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <p class="mb-0 text-sm text-muted">Belum ada data pengajuan perjalanan dinas
                                                terbaru.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('javascripts')
    {{-- Pastikan path ke Chart.js vendor benar dan sudah di-include di layout utama jika memungkinkan --}}
    <script src="{{ asset('assets/vendor/chart.js/dist/Chart.min.js') }}"></script>
    {{-- <script src="{{ asset('assets/vendor/chart.js/dist/Chart.extension.js') }}"></script> {{ Chart.extension.js might not be standard or needed for basic charts }} --}}

    <script>
        // Variabel global untuk Chart.js (versi sederhana dari Argon)
        var Charts = {
            colors: {
                gray: {
                    200: '#E9ECEF',
                    600: '#8898aa',
                    700: '#525f7f'
                },
                theme: {
                    'default': '#172b4d',
                    'primary': '#5e72e4', // Primary color for Argon
                    'secondary': '#f4f5f7',
                    'info': '#11cdef',
                    'success': '#2dce89',
                    'danger': '#f5365c',
                    'warning': '#fb6340'
                },
                black: '#12263F',
                white: '#FFFFFF',
                transparent: 'transparent',
            },
        };

        document.addEventListener('DOMContentLoaded', function() {
            var ctxDaerahTujuan = document.getElementById("chart-daerah-tujuan")?.getContext("2d");

            if (ctxDaerahTujuan) {
                var chartDaerahLabels = @json($chartDaerahLabels ?? []); // Data dari controller
                var chartDaerahData = @json($chartDaerahData ?? []); // Data dari controller

                if (chartDaerahLabels.length > 0 && chartDaerahData.length > 0) {
                    new Chart(ctxDaerahTujuan, {
                        type: "bar", // Changed to bar chart for this data type
                        data: {
                            labels: chartDaerahLabels,
                            datasets: [{
                                label: "Total Perjalanan", // Updated label
                                tension: 0.4,
                                borderWidth: 0, // Bar charts don't usually need a border width like lines
                                borderRadius: 4, // Rounded bars
                                pointRadius: 0,
                                borderColor: Charts.colors.theme.primary,
                                backgroundColor: Charts.colors.theme
                                    .primary, // Solid color for bars
                                data: chartDaerahData,
                                maxBarThickness: 20 // Adjust bar thickness as needed
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false, // Hide legend if only one dataset
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
                                        color: Charts.colors.gray[200]
                                    },
                                    ticks: {
                                        display: true,
                                        padding: 10,
                                        color: Charts.colors.gray[600],
                                        font: {
                                            size: 11,
                                            family: "Open Sans",
                                            style: 'normal',
                                            lineHeight: 2
                                        },
                                        precision: 0 // Only integer values for count
                                    }
                                },
                                x: {
                                    grid: {
                                        drawBorder: false,
                                        display: false, // Can hide x-axis grid lines for bar chart
                                    },
                                    ticks: {
                                        display: true,
                                        padding: 10,
                                        color: Charts.colors.gray[700], // Darker for labels
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
                    var chartContainer = document.getElementById("chart-daerah-tujuan")?.parentElement;
                    if (chartContainer) {
                        chartContainer.innerHTML =
                            "<p class='text-center p-5 text-muted'>Tidak ada data statistik daerah tujuan untuk ditampilkan.</p>";
                    }
                }
            }
        });
    </script>
@endpush
