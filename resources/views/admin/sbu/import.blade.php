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
                                    <li>Total Baris Data CSV Diproses: {{ $summary['total_csv_data_rows'] ?? 'N/A' }}</li>
                                    <li>Model Berhasil Dibuat (sebelum insert): {{ $summary['models_instantiated_count'] ?? 'N/A' }}</li>
                                    <li>Kegagalan Validasi Formal: {{ count($summary['validation_failures'] ?? []) }}</li>
                                    <li>Error Pembuatan Model/Lainnya: {{ count($summary['model_creation_errors'] ?? []) }}</li>
                                    {{-- <li>Berhasil Diimpor ke Database: {{ $summary['actually_imported_to_db'] ?? 'N/A' }}</li> --}}
                                </ul>
                            @endif
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger text-white" role="alert">
                           <strong>Error Server!</strong> {{ session('error') }}
                        </div>
                    @endif
                    @if (session('warning_toast'))
                        <div class="alert alert-warning text-dark" role="alert">
                            {{ session('warning_toast') }}
                        </div>
                    @endif

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

                    @if ($errors->has('sbu_file'))
                        <div class="alert alert-danger text-white mt-3" role="alert">
                           {{ $errors->first('sbu_file') }}
                        </div>
                    @endif

                    {{-- Petunjuk Pengisian Template --}}
                    <div class="mb-4 p-3 border rounded bg-light shadow-sm">
                        <h6 class="font-weight-bold text-dark"><i class="fas fa-info-circle me-2"></i>Petunjuk Pengisian Template CSV SBU:</h6>
                        <ol class="text-sm ps-3 mb-0">
                            <li class="mb-2">Unduh template CSV yang disediakan melalui tombol di bawah. **Jangan mengubah urutan atau nama header kolom** pada template.</li>
                            <li class="mb-1">Kolom yang **wajib diisi** di CSV (ditandai dengan <span class="text-danger fw-bold">*</span> pada form manual):
                                <ul class="ps-3">
                                    <li><code>kategori_biaya</code></li>
                                    <li><code>uraian_biaya</code></li>
                                    <li><code>satuan</code></li>
                                    <li><code>besaran_biaya</code></li>
                                </ul>
                            </li>
                            <li class="mb-1"><strong>Panduan Format Kolom:</strong>
                                <ul class="ps-3">
                                    <li><strong>kategori_biaya<span class="text-danger">*</span></strong>: Kategori utama biaya. Gunakan huruf kapital dan underscore (_) untuk spasi. Contoh: <code>UANG_HARIAN</code>, <code>PENGINAPAN</code>, <code>TRANSPORTASI_UDARA</code>, <code>TRANSPORTASI_DARAT_TAKSI</code>, <code>REPRESENTASI</code>, <code>TRANSPORTASI_ANTAR_KABUPATEN_PROVINSI</code>, <code>TRANSPORTASI_KECAMATAN_DESA</code>.</li>
                                    <li><strong>uraian_biaya<span class="text-danger">*</span></strong>: Deskripsi detail item SBU. Contoh: <code>Luar Kabupaten Aceh</code>, <code>Hotel Gol IV Riau</code>, <code>Taksi Bandara Pekanbaru PP</code>, <code>Siak ke Dayun (Ibukota Kecamatan)</code>.</li>
                                    <li><strong>provinsi_tujuan</strong>: Nama provinsi tujuan jika SBU berlaku untuk provinsi tertentu. Isi dengan huruf kapital. Contoh: <code>ACEH</code>, <code>RIAU</code>. Kosongkan jika tidak spesifik provinsi.</li>
                                    <li><strong>kota_tujuan</strong>: Nama kota/kabupaten tujuan jika SBU lebih spesifik. Isi dengan huruf kapital. Contoh: <code>SIAK</code>, <code>PEKANBARU</code>. Kosongkan jika tidak spesifik kota/kabupaten.</li>
                                    <li><strong>kecamatan_tujuan</strong>: Nama kecamatan tujuan (biasanya untuk transportasi internal). Contoh: <code>Dayun</code>, <code>Minas</code>. Kosongkan jika tidak spesifik kecamatan.</li>
                                    <li><strong>desa_tujuan</strong>: Nama desa/kampung tujuan. Contoh: <code>Rantau Bertuah</code>. Kosongkan jika tidak spesifik desa.</li>
                                    <li><strong>satuan<span class="text-danger">*</span></strong>: Satuan biaya. Contoh: <code>OH</code> (Orang Hari), <code>OK</code> (Orang Kali), <code>Kali</code>, <code>Tiket</code>, <code>PP</code> (Pulang Pergi), <code>KM</code>.</li>
                                    <li><strong>besaran_biaya<span class="text-danger">*</span></strong>: Jumlah biaya dalam angka. **Hanya angka, tanpa "Rp" atau pemisah ribuan (titik/koma).** Gunakan titik (.) sebagai pemisah desimal jika ada. Contoh: <code>150000</code> atau <code>360000.00</code>.</li>
                                    <li><strong>tipe_perjalanan</strong>: Jenis perjalanan yang terkait SBU ini. Gunakan huruf kapital dan underscore. Contoh: <code>DALAM_KABUPATEN_LEBIH_8_JAM</code>, <code>LUAR_DAERAH_LUAR_KABUPATEN</code>, <code>DIKLAT</code>, <code>Semua</code>. Kosongkan jika tidak spesifik.</li>
                                    <li><strong>tingkat_pejabat_atau_golongan</strong>: Tingkat pejabat atau golongan yang berhak. Gunakan huruf kapital dan underscore. Contoh: <code>KEPALA_DAERAH_ESELON_I</code>, <code>ESELON_II</code>, <code>ESELON_III_GOL_IV</code>, <code>GOL_II_I_NON_ASN</code>, <code>Semua</code>, <code>EKONOMI</code> (untuk tiket). Kosongkan jika berlaku umum.</li>
                                    <li><strong>keterangan</strong>: Catatan atau informasi tambahan terkait item SBU.</li>
                                    <li><strong>jarak_km_min</strong>: Angka (integer) jarak minimal dalam KM jika SBU berdasarkan rentang jarak. Kosongkan jika tidak.</li>
                                    <li><strong>jarak_km_max</strong>: Angka (integer) jarak maksimal dalam KM. Harus lebih besar atau sama dengan `jarak_km_min` jika keduanya diisi. Kosongkan jika tidak.</li>
                                </ul>
                            </li>
                            <li class="mb-1">Pastikan tidak ada baris kosong di tengah-tengah data Anda.</li>
                            <li class="mb-0">Simpan file dalam format **CSV (Comma delimited) (*.csv)** dengan encoding **UTF-8** untuk kompatibilitas terbaik.</li>
                        </ol>
                    </div>

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