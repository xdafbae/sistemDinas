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

        {{-- NEW ROW: User-Specific Perjalanan Dinas Card --}}
        <div class="row mt-4">
            <div class="col-lg-12 mb-lg-0 mb-4">
                <div class="card">
                    <div class="card-header pb-0 p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ $canViewAllData ? 'Perjalanan Dinas Per Personil' : 'Perjalanan Dinas Anda' }}</h6>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        @if($canViewAllData)
                            {{-- Admin View: Table of users with their perjalanan dinas count --}}
                            @if(isset($userPerjalananDinas) && count($userPerjalananDinas) > 0)
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Personil</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">NIP</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total Perjalanan</th>
                                                <th class="text-secondary opacity-7"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($userPerjalananDinas as $userData)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex px-2 py-1">
                                                            <div>
                                                                <div class="avatar avatar-sm me-3 bg-gradient-primary">
                                                                    <span class="text-white">{{ substr($userData->nama, 0, 1) }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex flex-column justify-content-center">
                                                                <h6 class="mb-0 text-sm">{{ $userData->nama }}</h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p class="text-xs font-weight-bold mb-0">{{ $userData->nip ?? '-' }}</p>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <span class="badge badge-sm bg-gradient-success">{{ $userData->total_perjalanan }}</span>
                                                    </td>
                                                    <td class="align-middle text-end">
                                                        <a href="#" class="text-secondary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Detail perjalanan">
                                                            Detail
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <p class="text-muted mb-0">Belum ada data perjalanan dinas untuk ditampilkan.</p>
                                </div>
                            @endif
                        @else
                            {{-- Regular User View: Personal Perjalanan Dinas Stats --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-stats mb-4 mb-xl-0">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col">
                                                    <h5 class="card-title text-uppercase text-muted mb-0">Total Perjalanan</h5>
                                                    <span class="h2 font-weight-bold mb-0">{{ $totalPerjalananUser ?? 0 }}</span>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                                                        <i class="ni ni-chart-bar-32"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="mt-3 mb-0 text-muted text-sm">
                                                <span class="text-nowrap">Sepanjang karir Anda</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card card-stats mb-4 mb-xl-0">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col">
                                                    <h5 class="card-title text-uppercase text-muted mb-0">Status Perjalanan</h5>
                                                </div>
                                            </div>
                                            <div class="progress-wrapper mt-3">
                                                <div class="progress-info">
                                                    <div class="progress-percentage">
                                                        <span class="text-success">Disetujui: {{ $perjalananDisetujui ?? 0 }}</span>
                                                    </div>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                        style="width: {{ isset($totalPerjalananUser) && $totalPerjalananUser > 0 ? (($perjalananDisetujui ?? 0) / $totalPerjalananUser * 100) : 0 }}%">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="progress-wrapper mt-3">
                                                <div class="progress-info">
                                                    <div class="progress-percentage">
                                                        <span class="text-warning">Diproses: {{ $perjalananDiproses ?? 0 }}</span>
                                                    </div>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-warning" role="progressbar" 
                                                        style="width: {{ isset($totalPerjalananUser) && $totalPerjalananUser > 0 ? (($perjalananDiproses ?? 0) / $totalPerjalananUser * 100) : 0 }}%">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="progress-wrapper mt-3">
                                                <div class="progress-info">
                                                    <div class="progress-percentage">
                                                        <span class="text-danger">Ditolak: {{ $perjalananDitolak ?? 0 }}</span>
                                                    </div>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-danger" role="progressbar" 
                                                        style="width: {{ isset($totalPerjalananUser) && $totalPerjalananUser > 0 ? (($perjalananDitolak ?? 0) / $totalPerjalananUser * 100) : 0 }}%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
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
            var ctxDaerahTujuan = document.getElementById("chart-daerah-tujuan");
            
            // Debug: Check if canvas element exists
            console.log("Canvas element:", ctxDaerahTujuan);
            
            if (ctxDaerahTujuan) {
                var ctx = ctxDaerahTujuan.getContext("2d");
                var chartDaerahLabels = @json($chartDaerahLabels ?? []); // Data dari controller
                var chartDaerahData = @json($chartDaerahData ?? []); // Data dari controller
                
                // Debug: Check if data is available
                console.log("Chart Labels:", chartDaerahLabels);
                console.log("Chart Data:", chartDaerahData);

                if (chartDaerahLabels.length > 0 && chartDaerahData.length > 0) {
                    // Generate an array of colors for the pie chart segments
                    var backgroundColors = [
                        Charts.colors.theme.primary,
                        Charts.colors.theme.success,
                        Charts.colors.theme.info,
                        Charts.colors.theme.warning,
                        Charts.colors.theme.danger
                    ];
                    
                    // Create the pie chart
                    var chart = new Chart(ctx, {
                        type: "pie", // Changed to pie chart
                        data: {
                            labels: chartDaerahLabels,
                            datasets: [{
                                label: "Total Perjalanan",
                                backgroundColor: backgroundColors,
                                data: chartDaerahData,
                                borderWidth: 2,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'right',
                                    labels: {
                                        boxWidth: 10,
                                        font: {
                                            size: 11,
                                            family: "Open Sans"
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            var label = context.label || '';
                                            var value = context.raw || 0;
                                            var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            var percentage = Math.round((value / total) * 100);
                                            return label + ': ' + value + ' (' + percentage + '%)';
                                        }
                                    }
                                }
                            },
                            cutout: '0%', // 0% for pie chart (would be higher for doughnut)
                        },
                    });
                    
                    // Debug: Check if chart was created
                    console.log("Chart created:", chart);
                } else {
                    console.log("No data available for chart");
                    var chartContainer = document.getElementById("chart-daerah-tujuan").parentElement;
                    if (chartContainer) {
                        chartContainer.innerHTML =
                            "<p class='text-center p-5 text-muted'>Tidak ada data statistik daerah tujuan untuk ditampilkan.</p>";
                    }
                }
            } else {
                console.error("Canvas element 'chart-daerah-tujuan' not found");
            }
        });
    </script>
@endpush
