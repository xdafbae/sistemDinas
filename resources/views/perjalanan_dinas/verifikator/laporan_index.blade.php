@extends('layouts.app')
@section('title', 'Verifikasi Laporan Perjalanan Dinas - ' . config('app.name'))
@section('page_name', 'Daftar Verifikasi Laporan Perjadin')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style> /* ... style DataTables ... */ </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Laporan Perjalanan Dinas Menunggu Verifikasi</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if (session('success'))<div class="alert alert-success text-white mx-3 my-2" role="alert">{{ session('success') }}</div>@endif
                    @if (session('error'))<div class="alert alert-danger text-white mx-3 my-2" role="alert">{{ session('error') }}</div>@endif
                    <div class="table-responsive p-3">
                        <table class="table table-striped table-hover align-items-center mb-0" id="verifikasi-laporan-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>No. SPT</th>
                                    <th>Tgl. SPT</th>
                                    <th>Pelapor</th>
                                    <th>Tujuan Perjadin</th>
                                    <th>Tgl. Laporan</th>
                                    <th class="text-end">Total Biaya Riil</th>
                                    <th class="text-center">Aksi</th>
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
    <script>
        $(document).ready(function() {
            var table = $('#verifikasi-laporan-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('verifikator.laporan-perjadin.index') }}", // Pastikan route ini benar
                    type: "GET", // Biasanya GET untuk mengambil data
                    error: function (xhr, error, code) { // Tambahkan error handling untuk AJAX
                        console.log(xhr);
                        console.log(code);
                        var RincianError = "";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            RincianError = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.error) {
                            RincianError = xhr.responseJSON.error;
                        } else if (xhr.responseText) {
                            try {
                                var jsonResponse = JSON.parse(xhr.responseText);
                                RincianError = jsonResponse.message || jsonResponse.error || xhr.responseText.substring(0, 200) + "...";
                            } catch (e) {
                                RincianError = xhr.responseText.substring(0, 200) + "... (Not a valid JSON response)";
                            }
                        }
                        // Tampilkan pesan error yang lebih ramah di tabel
                        $('#verifikasi-laporan-table_processing').hide(); // Sembunyikan "Processing..."
                        $('#verifikasi-laporan-table tbody').html(
                            '<tr><td colspan="8" class="text-center text-danger">Gagal memuat data. Silakan coba lagi.<br><small>' + RincianError + '</small></td></tr>'
                        );
                        console.error("Error DataTables AJAX: " + RincianError);
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nomor_spt', name: 'perjalananDinas.nomor_spt' }, // Sesuaikan 'name' untuk sorting/searching
                    { data: 'tanggal_spt', name: 'perjalananDinas.tanggal_spt' },
                    { data: 'pelapor_nama', name: 'pelapor.nama' },
                    { data: 'tujuan_spt', name: 'perjalananDinas.tujuan_spt' },
                    { data: 'tanggal_laporan_formatted', name: 'tanggal_laporan' },
                    { data: 'total_biaya_rill_dilaporkan', name: 'total_biaya_rill_dilaporkan', className:'text-end' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari No SPT, Tujuan, Pelapor...",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    infoEmpty: "Tidak ada laporan yang menunggu verifikasi",
                    infoFiltered: "(difilter dari _MAX_ total entri)",
                    zeroRecords: "Tidak ada data yang cocok ditemukan",
                    processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i><span class="sr-only">Memuat...</span>', // Indikator loading
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "<i class='fas fa-angle-right'></i>",
                        previous: "<i class='fas fa-angle-left'></i>"
                    },
                },
                order: [[5, 'asc']] // Order by tanggal laporan terlama (kolom ke-6, indeks 5)
            });

            // ... (inisialisasi tooltip sama seperti sebelumnya) ...
        });
    </script>
@endpush