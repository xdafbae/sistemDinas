@extends('layouts.app')
@section('title', 'Verifikasi Perjalanan Dinas - ' . config('app.name'))
@section('page_name', 'Daftar Verifikasi Perjalanan Dinas')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style> /* ... style DataTables ... */ </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Pengajuan Menunggu Verifikasi Anda</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if (session('success'))
                        <div class="alert alert-success text-white mx-3 my-2" role="alert">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger text-white mx-3 my-2" role="alert">{{ session('error') }}</div>
                    @endif
                    <div class="table-responsive p-3">
                        <table class="table table-striped table-hover align-items-center mb-0" id="verifikasi-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No. SPT</th>
                                    <th>Tgl. SPT</th>
                                    <th>Operator Pengaju</th>
                                    <th>Tujuan</th>
                                    <th>Personil</th>
                                    <th>Pelaksanaan</th>
                                    <th>Est. Biaya</th>
                                    <th>Aksi</th>
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
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#verifikasi-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('verifikator.perjalanan-dinas.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nomor_spt', name: 'nomor_spt' },
                    { data: 'tanggal_spt_formatted', name: 'tanggal_spt' },
                    { data: 'operator.nama', name: 'operator.nama', defaultContent: '-' }, // Menampilkan nama operator
                    { data: 'tujuan_spt', name: 'tujuan_spt' },
                    { data: 'personil_list', name: 'personil.nama', orderable:false, searchable:false },
                    { data: 'tanggal_pelaksanaan', name: 'tanggal_mulai' },
                    { data: 'total_estimasi_biaya', name: 'total_estimasi_biaya', className:'text-end' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                // ... (language DataTables) ...
                order: [[2, 'asc']] // Order by tanggal SPT terlama
            });
        });
    </script>
@endpush