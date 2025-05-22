@extends('layouts.app') {{-- Sesuaikan dengan layout utama Argon Anda --}}

@section('title', 'Unduh Dokumen Perjalanan Dinas - ' . config('app.name'))
@section('page_name', 'Dokumen SPT & SPPD') {{-- Untuk breadcrumb atau header di layout --}}

@push('styles')
    {{-- CSS DataTables dan ekstensi responsif --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    {{-- Opsional: CSS untuk tombol DataTables jika Anda menggunakannya --}}
    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css"> --}}
    <style>
        /* Kustomisasi kecil untuk tabel agar tidak terlalu mepet */
        #dokumen-perjalanan-dinas-table th,
        #dokumen-perjalanan-dinas-table td {
            white-space: nowrap; /* Mencegah teks wrap di sel tabel */
            vertical-align: middle;
        }
        .btn-group-sm>.btn, .btn-sm {
            padding: .25rem .5rem;
            font-size: .75rem; /* Ukuran font tombol lebih kecil */
        }
        .action-buttons .btn-group {
            margin-bottom: 0.25rem; /* Jarak antar grup tombol jika ada 2 baris */
        }
        .action-buttons .btn {
            min-width: 100px; /* Lebar minimum tombol agar teks tidak terpotong */
            margin-right: 0.2rem !important; /* Sedikit jarak antar tombol */
        }
        .action-buttons .btn:last-child {
            margin-right: 0 !important;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Daftar Dokumen Perjalanan Dinas (Status: Disetujui/Selesai)</h6>
                        {{-- Tombol Tambah tidak relevan di halaman download --}}
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if (session('success'))
                        <div class="alert alert-success text-white mx-3 my-2" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger text-white mx-3 my-2" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif
                    <div class="table-responsive p-3">
                        <table class="table table-striped table-hover align-items-center mb-0" id="dokumen-perjalanan-dinas-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No. SPT</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl. SPT</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tujuan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Personil</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pelaksanaan</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 no-export" width="220px">Aksi Download</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Data akan diisi oleh DataTables melalui AJAX --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- jQuery (pastikan sudah ada, biasanya dari layout utama) --}}
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    {{-- JS DataTables --}}
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    {{-- Opsional: DataTables Buttons (jika ingin fitur export, print, dll) --}}
    {{-- <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script> --}}

    <script>
        $(document).ready(function() {
            var table = $('#dokumen-perjalanan-dinas-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('dokumen.index') }}", // Route ke method index controller Dokumen
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nomor_spt', name: 'nomor_spt' },
                    { data: 'tanggal_spt_formatted', name: 'tanggal_spt' },
                    { data: 'tujuan_spt', name: 'tujuan_spt' },
                    { data: 'personil_list', name: 'personil.nama', orderable:false, searchable:false },
                    { data: 'tanggal_pelaksanaan', name: 'tanggal_mulai' }, // Bisa sort by tanggal_mulai
                    { data: 'status', name: 'status', className: 'text-center',
                        render: function(data, type, row) {
                            let statusText = data.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                            let badgeClass = 'bg-gradient-secondary'; // Default
                            if (data === 'disetujui') badgeClass = 'bg-gradient-success';
                            if (data === 'selesai') badgeClass = 'bg-gradient-primary';
                            // Tambahkan class lain untuk status lain jika perlu
                            return `<span class="badge ${badgeClass}">${statusText}</span>`;
                        }
                    },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center action-buttons' }
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari No SPT, Tujuan...",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    infoEmpty: "Tidak ada data yang tersedia",
                    infoFiltered: "(difilter dari _MAX_ total entri)",
                    zeroRecords: "Tidak ada data perjalanan dinas yang selesai/disetujui ditemukan.",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "<i class='fas fa-angle-right'></i>",
                        previous: "<i class='fas fa-angle-left'></i>"
                    },
                },
                order: [[2, 'desc']], // Default order by tanggal SPT terbaru
                // Opsional: Konfigurasi Buttons
                // dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                //      "<'row'<'col-sm-12'tr>>" +
                //      "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>" +
                //      "B", // 'B' untuk buttons (letakkan di akhir agar di bawah tabel)
                // buttons: [
                //     { extend: 'copyHtml5', className: 'btn-secondary btn-sm', exportOptions: { columns: ':not(.no-export)' } },
                //     { extend: 'excelHtml5', className: 'btn-success btn-sm', exportOptions: { columns: ':not(.no-export)' } },
                //     { extend: 'csvHtml5', className: 'btn-info btn-sm', exportOptions: { columns: ':not(.no-export)' } },
                //     { extend: 'pdfHtml5', className: 'btn-danger btn-sm', orientation: 'landscape', pageSize: 'LEGAL', exportOptions: { columns: ':not(.no-export)' } },
                //     { extend: 'print', className: 'btn-warning btn-sm', exportOptions: { columns: ':not(.no-export)' } },
                //     { extend: 'colvis', className: 'btn-light btn-sm dropdown-toggle', text: 'Pilih Kolom' }
                // ]
            });

            // Inisialisasi tooltip Bootstrap setelah DataTables selesai merender baris (jika ada)
            // atau jika Anda menambahkan tombol secara dinamis
            table.on('draw.dt', function () {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    // Hancurkan tooltip lama jika ada untuk menghindari duplikasi
                    var existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
                    if (existingTooltip) {
                        existingTooltip.dispose();
                    }
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });
            });
             // Inisialisasi awal juga
            var initialTooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var initialTooltipList = initialTooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });
    </script>
@endpush