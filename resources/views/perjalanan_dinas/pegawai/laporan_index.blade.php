@extends('layouts.app') {{-- Sesuaikan dengan layout Argon Anda --}}

@section('title', 'Laporan Perjalanan Dinas Saya - ' . config('app.name'))
@section('page_name', 'Laporan Perjalanan Dinas Saya')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        #laporan-perjadin-table th,
        #laporan-perjadin-table td {
            white-space: nowrap; /* Mencegah teks wrap di sel tabel */
            vertical-align: middle;
        }
        .btn-group .btn { margin-right: 0.2rem !important; }
        .btn-group .btn:last-child { margin-right: 0 !important; }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Daftar Perjalanan Dinas Saya</h6>
                        {{-- Tombol tambah tidak ada di sini, laporan dibuat dari perjalanan yang sudah selesai --}}
                    </div>
                     <p class="text-sm">Berikut adalah daftar perjalanan dinas yang telah disetujui/selesai dan dapat Anda buat atau lihat laporannya.</p>
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
                        <table class="table table-striped table-hover align-items-center mb-0" id="laporan-perjadin-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No. SPT</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl. SPT</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tujuan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pelaksanaan</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status SPT</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status Laporan</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 no-export" width="150px">Aksi Laporan</th>
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
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    {{-- JS untuk Buttons Export bisa ditambahkan jika diperlukan --}}

    <script>
        $(document).ready(function() {
            var table = $('#laporan-perjadin-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('pegawai.laporan-perjadin.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nomor_spt_display', name: 'nomor_spt' }, // Menggunakan _display karena ada fallback
                    { data: 'tanggal_spt_formatted', name: 'tanggal_spt' },
                    { data: 'tujuan_spt_display', name: 'tujuan_spt' }, // Menggunakan _display
                    { data: 'tanggal_pelaksanaan', name: 'tanggal_mulai' },
                    { data: 'status', name: 'status', className: 'text-center' }, // Status SPT dari PerjalananDinas
                    { data: 'status_laporan', name: 'laporanUtama.status_laporan', className: 'text-center', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari No SPT, Tujuan...",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri perjalanan dinas",
                    infoEmpty: "Tidak ada perjalanan dinas yang bisa dilaporkan",
                    infoFiltered: "(difilter dari _MAX_ total entri)",
                    zeroRecords: "Tidak ada data yang cocok ditemukan",
                    paginate: { /* ... */ }
                },
                order: [[2, 'desc']], // Default order by Tanggal SPT terbaru
                createdRow: function (row, data, dataIndex) {
                    // Tambahkan class berdasarkan status laporan untuk styling jika perlu
                    if (data.laporanUtama && data.laporanUtama.status_laporan === 'revisi_laporan') {
                        // $(row).addClass('table-warning');
                    }
                }
            });

            // Handling delete confirmation jika ada tombol delete di action (saat ini tidak ada)
            $('#laporan-perjadin-table').on('submit', '.delete-laporan-form', function(e) {
                if (!confirm('Apakah Anda yakin ingin menghapus draft laporan ini?')) {
                    e.preventDefault();
                }
            });

            // Inisialisasi tooltip Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            table.on('draw.dt', function () {
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