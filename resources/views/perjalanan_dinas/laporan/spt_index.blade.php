@extends('layouts.app') {{-- Sesuaikan dengan layout Argon Anda --}}

@section('title', 'Laporan Surat Perintah Tugas (SPT) - ' . config('app.name'))
@section('page_name', 'Laporan SPT Diterbitkan')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css"> {{-- CSS untuk Buttons --}}
    <style>
        #laporan-spt-table th,
        #laporan-spt-table td {
            white-space: nowrap;
            vertical-align: middle;
        }
        .dt-buttons .btn { /* Styling untuk tombol export */
            margin-left: 0.5rem;
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
                        <h6>Daftar Surat Perintah Tugas (SPT) yang Telah Diterbitkan</h6>
                        {{-- Tidak ada tombol tambah di sini --}}
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-3">
                        <table class="table table-striped table-hover align-items-center mb-0" id="laporan-spt-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nomor SPT</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal SPT</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Jenis SPT</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kota/Lokasi Tujuan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Dasar SPT</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Uraian SPT</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Jumlah Personil</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Personil</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Lama Hari</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal Mulai</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal Selesai</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Data akan diisi oleh DataTables --}}
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
    {{-- jQuery (pastikan sudah ada) --}}
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    {{-- JS DataTables --}}
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    {{-- JS untuk DataTables Buttons (Export Excel, dll.) --}}
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script> {{-- Diperlukan untuk Excel --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script> {{-- Diperlukan untuk PDF --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script> {{-- Font untuk PDF --}}
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script> {{-- Export HTML5 (Excel, CSV, PDF) --}}
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#laporan-spt-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('laporan.spt.data') }}", // Route ke method dataTableSPT
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nomor_spt', name: 'nomor_spt' },
                    { data: 'tanggal_spt', name: 'tanggal_spt' }, // Sudah diformat di controller
                    { data: 'jenis_spt', name: 'jenis_spt' },     // Sudah diformat di controller
                    { data: 'kota_tujuan_display', name: 'kota_tujuan_id' }, // Kolom custom dari controller
                    { data: 'dasar_spt', name: 'dasar_spt' },         // Sudah di-limit di controller
                    { data: 'uraian_spt', name: 'uraian_spt' },       // Sudah di-limit di controller
                    { data: 'jumlah_personil', name: 'jumlah_personil', className: 'text-center', orderable: false, searchable: false },
                    { data: 'nama_personil', name: 'personil.nama', orderable: false, searchable: false }, // Searchable false untuk relasi sederhana
                    { data: 'lama_hari', name: 'lama_hari', className: 'text-center' },
                    { data: 'tanggal_mulai_spt', name: 'tanggal_mulai' },
                    { data: 'tanggal_selesai_spt', name: 'tanggal_selesai' }
                    // { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' } // Jika ada kolom aksi
                ],
                language: { /* ... (terjemahan DataTables seperti sebelumnya) ... */ },
                order: [[2, 'desc']], // Default order by Tanggal SPT terbaru
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>" +
                     "<'row'<'col-sm-12 mt-2 text-end'B>>", // 'B' untuk Buttons, diletakkan di bawah
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Export Excel',
                        className: 'btn btn-success btn-sm',
                        titleAttr: 'Export to Excel',
                        title: 'Laporan SPT Diterbitkan - {{ config("app.name") }} - {{ now()->translatedFormat("d F Y") }}', // Judul file Excel
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11] // Pilih kolom yang akan diexport (sesuaikan indeksnya)
                        }
                    },
                    {
                        extend: 'colvis',
                        className: 'btn btn-light btn-sm dropdown-toggle',
                        text: 'Pilih Kolom'
                    }
                ]
            });

            // Inisialisasi tooltip Bootstrap (jika ada tombol dengan tooltip)
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            $('#laporan-spt-table').on('draw.dt', function () {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    var existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
                    if (existingTooltip) { existingTooltip.dispose(); }
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });
            });
        });
    </script>
@endpush