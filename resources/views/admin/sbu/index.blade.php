@extends('layouts.app')
@section('title', 'Manajemen SBU - ' . config('app.name'))
@section('page_name', 'Manajemen Standar Biaya Umum (SBU)')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <style>
        #sbu-table th,
        #sbu-table td {
            white-space: nowrap;
            vertical-align: middle;
        }

        .dt-buttons .btn {
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
                        {{-- Di card-header index.blade.php --}}
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Daftar Item SBU</h6>
                            <div>
                                <a href="{{ route('admin.sbu.import.form') }}" class="btn btn-info btn-sm mb-0 me-2">
                                    <i class="fas fa-file-import me-1"></i> Import SBU
                                </a>
                                <a href="{{ route('admin.sbu.create') }}" class="btn btn-primary btn-sm mb-0">
                                    <i class="fas fa-plus me-1"></i> Tambah Item SBU
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        @if (session('success'))
                            <div class="alert alert-success text-white mx-3 my-2" role="alert">{{ session('success') }}
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger text-white mx-3 my-2" role="alert">{{ session('error') }}</div>
                        @endif
                        <div class="table-responsive p-3">
                            <table class="table table-striped table-hover align-items-center mb-0" id="sbu-table"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th>Kategori Biaya</th>
                                        <th>Uraian Biaya</th>
                                        <th>Prov/Kota/Kec/Desa Tujuan</th>
                                        <th>Satuan</th>
                                        <th class="text-end">Besaran (Rp)</th>
                                        <th>Tipe Perjalanan</th>
                                        <th>Tingkat/Golongan</th>
                                        <th>Jarak (KM)</th>
                                        <th>Keterangan</th>
                                        <th class="text-center no-export" width="100px">Aksi</th>
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
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#sbu-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('admin.sbu.index') }}", // DataTables akan otomatis memanggil ini
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'kategori_biaya',
                        name: 'kategori_biaya'
                    },
                    {
                        data: 'uraian_biaya',
                        name: 'uraian_biaya'
                    },
                    {
                        data: null,
                        name: 'tujuan',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            let tujuan = [];
                            if (row.provinsi_tujuan) tujuan.push("Prov: " + row.provinsi_tujuan);
                            if (row.kota_tujuan) tujuan.push("Kota/Kab: " + row.kota_tujuan);
                            if (row.kecamatan_tujuan) tujuan.push("Kec: " + row.kecamatan_tujuan);
                            if (row.desa_tujuan) tujuan.push("Desa: " + row.desa_tujuan);
                            return tujuan.length > 0 ? tujuan.join('<br>') : '-';
                        }
                    },
                    {
                        data: 'satuan',
                        name: 'satuan'
                    },
                    {
                        data: 'besaran_biaya',
                        name: 'besaran_biaya',
                        className: 'text-end'
                    },
                    {
                        data: 'tipe_perjalanan',
                        name: 'tipe_perjalanan'
                    },
                    {
                        data: 'tingkat_pejabat_atau_golongan',
                        name: 'tingkat_pejabat_atau_golongan'
                    },
                    {
                        data: null,
                        name: 'jarak',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            if (row.jarak_km_min !== null || row.jarak_km_max !== null) {
                                return (row.jarak_km_min !== null ? row.jarak_km_min : '0') +
                                    " - " + (row.jarak_km_max !== null ? row.jarak_km_max : '∞') +
                                    " KM";
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'keterangan',
                        name: 'keterangan',
                        defaultContent: '-',
                        render: function(data) {
                            return data ? data.substr(0, 30) + (data.length > 30 ? '...' : '') :
                            '-';
                        }
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
                    /* ... terjemahan ... */ },
                order: [
                    [1, 'asc'],
                    [2, 'asc']
                ], // Order by kategori, lalu uraian
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>" +
                    "<'row'<'col-sm-12 mt-2 text-end'B>>",
                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        titleAttr: 'Export Excel',
                        title: 'Daftar SBU - {{ config('app.name') }}',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                        }
                    },
                    {
                        extend: 'colvis',
                        className: 'btn btn-light btn-sm dropdown-toggle',
                        text: 'Pilih Kolom'
                    }
                ]
            });
            $('#sbu-table').on('submit', '.delete-form', function(e) {
                if (!confirm('Apakah Anda yakin ingin menghapus item SBU ini?')) {
                    e.preventDefault();
                }
            });
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            table.on('draw.dt', function() {
                /* Inisialisasi ulang tooltip jika perlu */ });
        });
    </script>
@endpush
