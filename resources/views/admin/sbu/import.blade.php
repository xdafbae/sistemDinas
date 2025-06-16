@extends('layouts.app')
@section('title', 'Import Data SBU - ' . config('app.name'))
@section('page_name', 'Import Data SBU dari CSV')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-10 col-md-12 mx-auto">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Import Data SBU</h6>
                    </div>
                </div>
                <div class="card-body px-4 pb-3">
                    @if (session('success'))
                        <div class="alert alert-success text-white" role="alert">
                            {{ session('success') }}
                            @if(session('import_summary_details'))
                                @php $summary = session('import_summary_details'); @endphp
                                <hr class="my-2 border-light">
                                <p class="mb-0 text-sm">Ringkasan Import:</p>
                                <ul class="mb-0 text-sm" style="padding-left: 20px;">
                                    <li>Total Baris Data CSV Diproses: {{ $summary['total_csv_rows_processed_by_model_method'] }}</li>
                                    <li>Model Berhasil Dibuat (sebelum insert): {{ $summary['models_successfully_instantiated'] }}</li>
                                    <li>Kegagalan Validasi Formal: {{ count($summary['validation_failures'] ?? []) }}</li>
                                    <li>Error Pembuatan Model/Lainnya: {{ count($summary['model_creation_errors'] ?? []) }}</li>
                                    {{-- Anda bisa menambahkan jumlah data aktual yang masuk ke DB jika perlu --}}
                                </ul>
                            @endif
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger text-white" role="alert">
                           <strong>Error Server!</strong> {{ session('error') }}
                        </div>
                    @endif
                    @if (session('warning_toast')) {{-- Untuk pesan toast dari controller --}}
                        <div class="alert alert-warning text-dark" role="alert">
                            {{ session('warning_toast') }}
                        </div>
                    @endif

                    {{-- Menampilkan error validasi per baris dari Maatwebsite/Excel atau error pembuatan model --}}
                    @if (session('import_errors') && is_array(session('import_errors')) && count(session('import_errors')) > 0)
                        <div class="alert alert-danger text-white" role="alert">
                            <strong>Beberapa baris data tidak dapat diimpor atau dilewati karena kesalahan berikut:</strong>
                            <ul style="list-style-type: disc; padding-left: 20px; max-height: 250px; overflow-y: auto; font-size: 0.8rem;" class="mt-2">
                                @foreach (session('import_errors') as $errorMessage)
                                    <li>{{ $errorMessage }}</li>
                                @endforeach
                            </ul>
                            <p class="mt-2 mb-0">Silakan perbaiki file CSV Anda pada baris yang disebutkan dan coba impor kembali.</p>
                        </div>
                    @endif

                    {{-- Menampilkan error validasi umum dari Laravel (misal, file tidak dipilih) --}}
                    @if ($errors->has('sbu_file'))
                        <div class="alert alert-danger text-white mt-3" role="alert">
                           {{ $errors->first('sbu_file') }}
                        </div>
                    @endif

                    {{-- ... (Petunjuk Pengisian Template seperti sebelumnya) ... --}}
                    <div class="mb-4 p-3 border rounded bg-light"> /* ... Petunjuk ... */ </div>


                    <a href="{{ route('admin.sbu.download.template') }}" class="btn btn-info mb-4">
                        <i class="fas fa-download me-2"></i>Unduh Template CSV
                    </a>
                    <hr class="horizontal dark">

                    <form action="{{ route('admin.sbu.import.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="sbu_file" class="form-label">Pilih File CSV untuk Diimpor <span class="text-danger">*</span></label>
                            <input class="form-control @error('sbu_file') is-invalid @enderror" type="file" id="sbu_file" name="sbu_file" accept=".csv,text/csv" required>
                            @error('sbu_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Format file yang didukung: .csv. Maksimal ukuran: 5MB.</small>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('admin.sbu.index') }}" class="btn btn-secondary mt-4">Kembali ke Daftar SBU</a>
                            <button type="submit" class="btn btn-primary mt-4">
                                <i class="fas fa-upload me-2"></i>Import Data SBU
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 
@endsection