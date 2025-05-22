@extends('layouts.app')

@section('title', 'Daftar Pengajuan Perjalanan Dinas - ' . config('app.name'))
@section('page_name', 'Pengajuan Perjalanan Dinas Saya')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        #perjalanan-dinas-table th, #perjalanan-dinas-table td { white-space: nowrap; vertical-align: middle;}
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
                        <h6>Daftar Pengajuan Perjalanan Dinas Saya</h6>
                        <a href="{{ route('operator.perjalanan-dinas.create') }}" class="btn btn-primary btn-sm mb-0">
                            <i class="fas fa-plus me-1"></i> Buat Pengajuan Baru
                        </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if (session('success'))
                        <div class="alert alert-success text-white mx-3 my-2" role="alert">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger text-white mx-3 my-2" role="alert">{{ session('error') }}</div>
                    @endif
                    <div class="table-responsive p-3">
                        <table class="table table-striped table-hover align-items-center mb-0" id="perjalanan-dinas-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No. SPT</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl. SPT</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tujuan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Personil</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pelaksanaan</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Est. Biaya</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 no-export" width="120px">Aksi</th>
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
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#perjalanan-dinas-table').DataTable({
                processing: true, serverSide: true, responsive: true,
                ajax: "{{ route('operator.perjalanan-dinas.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nomor_spt', name: 'nomor_spt' },
                    { data: 'tanggal_spt_formatted', name: 'tanggal_spt' },
                    { data: 'tujuan_spt', name: 'tujuan_spt' },
                    { data: 'personil_list', name: 'personil.nama', orderable:false, searchable:false },
                    { data: 'tanggal_pelaksanaan', name: 'tanggal_mulai' },
                    { data: 'status', name: 'status', className: 'text-center' },
                    { data: 'total_estimasi_biaya', name: 'total_estimasi_biaya', className: 'text-end' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: { /* ... terjemahan ... */ },
                order: [[2, 'desc']]
            });
            $('#perjalanan-dinas-table').on('submit', '.delete-form', function(e) { if (!confirm('Apakah Anda yakin ingin menghapus pengajuan ini?')) { e.preventDefault(); }});
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl) });
            table.on('draw.dt', function () { /* Inisialisasi ulang tooltip jika perlu */ });
        });
    </script>
@endpush