@extends('layouts.app')
@section('title', 'Persetujuan Perjalanan Dinas - ' . config('app.name'))
@section('page_name', 'Daftar Persetujuan Perjalanan Dinas')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        #persetujuan-table th, #persetujuan-table td { white-space: nowrap; vertical-align: middle; }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Pengajuan Menunggu Persetujuan Anda</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if (session('success'))
                        <div class="alert alert-success text-white mx-3 my-2" role="alert">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger text-white mx-3 my-2" role="alert">{{ session('error') }}</div>
                    @endif
                    <div class="table-responsive p-3">
                        <table class="table table-striped table-hover align-items-center mb-0" id="persetujuan-table" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No. SPT Sementara</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl. SPT Awal</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Operator</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Verifikator</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tujuan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Personil</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pelaksanaan</th>
                                    <th class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Est. Biaya</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
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
            $('#persetujuan-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('atasan.perjalanan-dinas.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nomor_spt', name: 'nomor_spt', render: function(data,type,row){ return data ? data : '<span class="text-muted fst-italic">Belum Ada</span>'; } },
                    { data: 'tanggal_spt_formatted', name: 'tanggal_spt' },
                    { data: 'operator_nama', name: 'operator.nama', defaultContent: '-' },
                    { data: 'verifikator_nama', name: 'verifikator.nama', defaultContent: '-' },
                    { data: 'tujuan_spt', name: 'tujuan_spt' },
                    { data: 'personil_list', name: 'personil.nama', orderable:false, searchable:false },
                    { data: 'tanggal_pelaksanaan', name: 'tanggal_mulai' }, // Bisa sort by tanggal_mulai
                    { data: 'total_estimasi_biaya', name: 'total_estimasi_biaya', className:'text-end' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: { /* ... terjemahan ... */ },
                order: [[2, 'asc']] // Order by tanggal SPT awal terlama
            });

            // Inisialisasi tooltip Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            $('#persetujuan-table').on('draw.dt', function () {
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