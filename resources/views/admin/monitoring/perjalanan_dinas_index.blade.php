@extends('layouts.app')
@section('title', 'Monitoring Perjalanan Dinas - ' . config('app.name'))
@section('page_name', 'Monitoring Semua Perjalanan Dinas')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <style>
        #monitoring-perjadin-table th,
        #monitoring-perjadin-table td {
            white-space: nowrap;
            vertical-align: middle;
            font-size: 0.8rem;
            /* Ukuran font lebih kecil untuk tabel padat */
        }

        .dt-buttons .btn {
            margin-left: 0.5rem;
        }

        .filters-card .form-label {
            margin-bottom: 0.25rem;
            font-size: 0.75rem;
        }

        .filters-card .form-select-sm,
        .filters-card .form-control-sm {
            font-size: 0.75rem;
            padding: .25rem .5rem;
            height: auto;
        }

        .filters-card .btn-sm {
            padding: .25rem .5rem;
            font-size: .75rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <div class="card mb-4 filters-card">
            <div class="card-header pb-0 pt-3">
                <h6 class="mb-0"><i class="fas fa-filter me-1"></i> Filter Data Perjalanan Dinas</h6>
            </div>
            <div class="card-body pt-2 pb-3">
                <form id="filter-form" class="row gx-2 gy-2 align-items-center">
                    <div class="col-md-3 col-sm-6">
                        <label for="status_filter" class="form-label">Status Pengajuan:</label>
                        <select class="form-select form-select-sm" id="status_filter" name="status_filter">
                            <option value="semua">Semua Status</option>
                            @foreach ($allStatus as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label for="jenis_spt_filter" class="form-label">Jenis SPT:</label>
                        <select class="form-select form-select-sm" id="jenis_spt_filter" name="jenis_spt_filter">
                            <option value="semua">Semua Jenis SPT</option>
                            @foreach ($jenisSPTList as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label for="tanggal_spt_mulai" class="form-label">Tgl SPT Mulai:</label>
                        <input type="date" name="tanggal_spt_mulai" id="tanggal_spt_mulai"
                            class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <label for="tanggal_spt_selesai" class="form-label">Tgl SPT Selesai:</label>
                        <input type="date" name="tanggal_spt_selesai" id="tanggal_spt_selesai"
                            class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 col-sm-12 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Terapkan</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100 ms-2"
                            id="reset-filter-btn">Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Semua Data Pengajuan Perjalanan Dinas</h6>
                            @can('submit perjalanan dinas')
                                {{-- Jika superadmin juga bisa buat pengajuan --}}
                                <a href="{{ route('operator.perjalanan-dinas.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i> Buat Pengajuan
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-3">
                            <table class="table table-striped table-hover align-items-center mb-0"
                                id="monitoring-perjadin-table" style="width:100%">
                                <thead>
                                    <tr>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            No</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No.
                                            SPT</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl.
                                            SPT</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Operator</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Tujuan</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Jenis SPT</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Personil</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Pelaksanaan</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Status</th>
                                        <th
                                            class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Est. Biaya</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 no-export">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script> {{-- Untuk Excel --}}
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#monitoring-perjadin-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.monitoring.perjadin.index') }}", 
                    type: "GET", // Pastikan method GET
                    data: function(d) {
                        d.status_filter = $('#status_filter').val();
                        d.jenis_spt_filter = $('#jenis_spt_filter').val();
                        d.tanggal_spt_mulai = $('#tanggal_spt_mulai').val();
                        d.tanggal_spt_selesai = $('#tanggal_spt_selesai').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'nomor_spt_display',
                        name: 'perjalanan_dinas.nomor_spt'
                    }, // Sorting/searching by original column
                    {
                        data: 'tanggal_spt_formatted',
                        name: 'perjalanan_dinas.tanggal_spt'
                    },
                    {
                        data: 'operator_nama',
                        name: 'operator.nama'
                    }, // Sorting/searching by operator name
                    {
                        data: 'tujuan_display',
                        name: 'perjalanan_dinas.tujuan_spt'
                    },
                    {
                        data: 'jenis_spt_display',
                        name: 'perjalanan_dinas.jenis_spt'
                    },
                    {
                        data: 'personil_list',
                        name: 'personil.nama',
                        orderable: false,
                        searchable: true
                    }, // Memungkinkan search by nama personil
                    {
                        data: 'tanggal_pelaksanaan',
                        name: 'perjalanan_dinas.tanggal_mulai'
                    },
                    {
                        data: 'status',
                        name: 'perjalanan_dinas.status',
                        className: 'text-center'
                    },
                    {
                        data: 'total_estimasi_biaya',
                        name: 'perjalanan_dinas.total_estimasi_biaya',
                        className: 'text-end'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari (No.SPT, Tujuan, Operator, Personil)...",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_-_END_ dari _TOTAL_ entri",
                    infoEmpty: "Data tidak ditemukan",
                    infoFiltered: "(difilter dari _MAX_ total entri)",
                    zeroRecords: "Tidak ada data perjalanan dinas yang cocok ditemukan",
                    paginate: {
                        first: "<i class='fas fa-angle-double-left'></i>",
                        last: "<i class='fas fa-angle-double-right'></i>",
                        next: "<i class='fas fa-angle-right'></i>",
                        previous: "<i class='fas fa-angle-left'></i>"
                    }
                },
                order: [
                    [2, 'desc']
                ], // Default order by Tanggal SPT terbaru
                dom: "<'row'<'col-sm-12 col-md-auto'l><'col-sm-12 col-md-auto'B><'col-sm-12 col-md ms-auto'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Export Excel',
                        className: 'btn btn-success btn-sm',
                        titleAttr: 'Export Excel',
                        title: 'Monitoring Perjalanan Dinas - {{ config('app.name') }} - {{ now()->translatedFormat('d F Y') }}',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8,
                                9
                            ] // Indeks kolom yang akan diexport (sesuaikan)
                        }
                    },
                    {
                        extend: 'colvis',
                        className: 'btn btn-light btn-sm dropdown-toggle',
                        text: '<i class="fas fa-columns"></i> Pilih Kolom'
                    }
                ]
            });

            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });

            $('#reset-filter-btn').on('click', function() {
                $('#filter-form')[0].reset(); // Reset form filter
                $('#status_filter').val('semua').trigger(
                    'change'); // Kembalikan ke default jika pakai select2
                $('#jenis_spt_filter').val('semua').trigger('change');
                table.ajax.reload();
            });

            // Inisialisasi tooltip Bootstrap setelah DataTables selesai merender baris
            table.on('draw.dt', function() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                    '[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                    var existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
                    if (existingTooltip) {
                        existingTooltip.dispose();
                    }
                    new bootstrap.Tooltip(tooltipTriggerEl);
                });
            });
            // Inisialisasi awal juga
            var initialTooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            initialTooltipTriggerList.forEach(function(tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endpush
