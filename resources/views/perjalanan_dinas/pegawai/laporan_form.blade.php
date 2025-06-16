@extends('layouts.app')
@section('title', ($laporan->exists ? 'Edit' : 'Buat') . ' Laporan Perjadin - SPT No: ' . Str::limit($perjalananDinas->nomor_spt ?? 'N/A', 20))
@section('page_name', ($laporan->exists ? 'Edit' : 'Buat') . ' Laporan Perjalanan Dinas')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .rincian-item { background-color: #f8f9fa; }
        .btn-sm { padding: .25rem .5rem; font-size: .75rem; }
        /* Style untuk Select2 agar serasi dengan Argon */
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered { padding-right: 2.5rem; padding-top: .3rem; padding-bottom: .3rem; }
        .select2-container .select2-selection--multiple,
        .select2-container .select2-selection--single { min-height: calc(1.5em + 1rem + 2px); border-color: #d2d6da; padding-top: 0.375rem; padding-bottom: 0.375rem; }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered { line-height: 1.5; }
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection { border-color: #5e72e4; box-shadow: 0 0 0 0.2rem rgba(94, 114, 228, 0.25); }
        select.is-invalid + .select2-container--bootstrap-5 .select2-selection,
        .form-control.is-invalid + .select2-container--bootstrap-5 .select2-selection,
        #provinsi_tujuan_id.is-invalid + .select2-container--bootstrap-5 .select2-selection, /* Contoh jika select2 ada di form lain */
        #personil_ids.is-invalid + .select2-container--bootstrap-5 .select2-selection { /* Contoh jika select2 ada di form lain */
             border-color: #fd5c70 !important;
        }
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-search__field { margin-top: 0.5rem; }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-10 col-md-12 mx-auto">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">{{ ($laporan->exists ? 'Edit' : 'Form Isian') . ' Laporan Perjalanan Dinas' }}</h6>
                        <p class="text-white text-sm ps-3 mb-0">SPT Nomor: {{ $perjalananDinas->nomor_spt ?? 'Belum Ditetapkan' }}</p>
                    </div>
                </div>
                <div class="card-body px-4 pb-3">
                    @if (session('success'))<div class="alert alert-success text-white" role="alert">{{ session('success') }}</div>@endif
                    @if (session('error'))<div class="alert alert-danger text-white" role="alert"><strong>Error!</strong> {{ session('error') }}</div>@endif
                    @if (session('info'))<div class="alert alert-info text-white" role="alert">{{ session('info') }}</div>@endif

                    @if ($errors->any())
                        <div class="alert alert-danger text-white" role="alert">
                            <strong class="d-block">Perhatian! Terdapat kesalahan input:</strong>
                            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif
                    @if (session('import_errors') && is_array(session('import_errors')) && count(session('import_errors')) > 0) {{-- Contoh dari SBU import --}}
                        <div class="alert alert-warning text-dark" role="alert">
                            <strong>Beberapa baris data tidak dapat diimpor karena kesalahan validasi berikut:</strong>
                            <ul style="list-style-type: disc; padding-left: 20px; max-height: 200px; overflow-y: auto;" class="mt-2">
                                @foreach (session('import_errors') as $errorMessage)
                                    <li><small>{{ $errorMessage }}</small></li>
                                @endforeach
                            </ul>
                            <p class="mt-2 mb-0">Silakan perbaiki file CSV Anda pada baris yang disebutkan dan coba impor kembali.</p>
                        </div>
                    @endif


                    {{-- Informasi Perjalanan Dinas (Readonly) --}}
                    <div class="mb-4 p-3 border rounded bg-light">
                        <h6 class="text-dark font-weight-bolder text-sm">Detail Perjalanan Dinas</h6>
                        <p class="text-sm mb-1"><strong>Tujuan:</strong> {{ $perjalananDinas->tujuan_spt }}
                            @if($perjalananDinas->kota_tujuan_id || $perjalananDinas->provinsi_tujuan_id)
                                ({{ $perjalananDinas->kota_tujuan_id ?? '' }}{{ $perjalananDinas->kota_tujuan_id && $perjalananDinas->provinsi_tujuan_id ? ', ' : '' }}{{ $perjalananDinas->provinsi_tujuan_id ?? '' }})
                            @endif
                        </p>
                        <p class="text-sm mb-1"><strong>Tanggal Pelaksanaan:</strong> {{ $perjalananDinas->tanggal_mulai->translatedFormat('d M Y') }} s/d {{ $perjalananDinas->tanggal_selesai->translatedFormat('d M Y') }} ({{ $perjalananDinas->lama_hari }} hari)</p>
                        <p class="text-sm mb-0"><strong>Personil yang Ditugaskan:</strong> {{ $perjalananDinas->personil->pluck('nama')->implode(', ') }}</p>
                        <p class="text-sm mb-0"><strong>Pelapor:</strong> {{ Auth::user()->nama }}</p>
                    </div>


                    <form action="{{ route('pegawai.laporan-perjadin.storeOrUpdate', $perjalananDinas->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        {{-- Method PUT jika edit (controller sudah menangani firstOrCreate) --}}
                        {{-- @if($laporan->exists) @method('PUT') @endif --}}

                        <h6 class="mt-2 text-sm font-weight-bold">Isian Laporan</h6>
                        <hr class="horizontal dark mt-1 mb-3">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_laporan" class="form-label">Tanggal Laporan <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('tanggal_laporan') is-invalid @enderror" id="tanggal_laporan" name="tanggal_laporan" value="{{ old('tanggal_laporan', $laporan->tanggal_laporan ? $laporan->tanggal_laporan->format('Y-m-d') : date('Y-m-d')) }}" required>
                                @error('tanggal_laporan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="ringkasan_hasil_kegiatan" class="form-label">Ringkasan Hasil Kegiatan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('ringkasan_hasil_kegiatan') is-invalid @enderror" id="ringkasan_hasil_kegiatan" name="ringkasan_hasil_kegiatan" rows="5" required placeholder="Jelaskan secara ringkas hasil utama dari kegiatan perjalanan dinas ini.">{{ old('ringkasan_hasil_kegiatan', $laporan->ringkasan_hasil_kegiatan) }}</textarea>
                            @error('ringkasan_hasil_kegiatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="kendala_dihadapi" class="form-label">Kendala yang Dihadapi (Jika Ada)</label>
                            <textarea class="form-control @error('kendala_dihadapi') is-invalid @enderror" id="kendala_dihadapi" name="kendala_dihadapi" rows="3">{{ old('kendala_dihadapi', $laporan->kendala_dihadapi) }}</textarea>
                            @error('kendala_dihadapi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="saran_tindak_lanjut" class="form-label">Saran / Tindak Lanjut (Jika Ada)</label>
                            <textarea class="form-control @error('saran_tindak_lanjut') is-invalid @enderror" id="saran_tindak_lanjut" name="saran_tindak_lanjut" rows="3">{{ old('saran_tindak_lanjut', $laporan->saran_tindak_lanjut) }}</textarea>
                            @error('saran_tindak_lanjut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>


                        <h6 class="mt-4 text-sm font-weight-bold">Rincian Biaya Riil</h6>
                        <hr class="horizontal dark mt-1 mb-3">

                        @if($estimasiBiaya->isNotEmpty())
                        <div class="alert alert-light p-3 mb-3 border">
                            <p class="fw-bold text-sm mb-1">Estimasi Biaya Awal (Sebagai Referensi):</p>
                            <ul class="list-unstyled mb-0">
                                @foreach($estimasiBiaya as $est)
                                <li class="text-sm d-flex justify-content-between">
                                    <span>{{ $loop->iteration }}. {{ $est->deskripsi_biaya }} @if($est->userTerkait) <small>(utk {{ Str::words($est->userTerkait->nama, 2, '') }})</small>@endif</span>
                                    <span>Rp {{ number_format($est->subtotal_biaya,0,',','.') }}</span>
                                </li>
                                @endforeach
                                 <li class="text-sm d-flex justify-content-between fw-bold border-top pt-1 mt-1">
                                    <span>TOTAL ESTIMASI</span>
                                    <span>Rp {{ number_format($perjalananDinas->total_estimasi_biaya,0,',','.') }}</span>
                                </li>
                            </ul>
                        </div>
                        @endif

                        <div id="rincian-biaya-container">
                            {{-- Loop untuk rincian biaya yang sudah ada (saat edit atau validasi gagal) --}}
                            @php
                                // Inisialisasi rincianItemIndex di sini untuk digunakan oleh JavaScript nanti
                                // Ambil dari old input dulu jika ada (saat validasi gagal)
                                $oldRincianBiaya = old('rincian_biaya', []);
                                $oldExistingRincianBiaya = old('existing_rincian_biaya', []);
                                $nextRincianIndex = 0;
                            @endphp

                            @if(!empty($oldExistingRincianBiaya))
                                @foreach($oldExistingRincianBiaya as $id_rincian => $rincian)
                                    @php $nextRincianIndex = max($nextRincianIndex, intval($id_rincian)) +1; @endphp
                                    @include('perjalanan_dinas.pegawai._rincian_biaya_item', ['index' => $id_rincian, 'rincian' => (object)$rincian, 'existing' => true])
                                @endforeach
                            @elseif($rincianBiayaRill->isNotEmpty() && empty(old('rincian_biaya'))) {{-- Hanya tampilkan dari DB jika tidak ada old rincian baru --}}
                                @foreach($rincianBiayaRill as $rincian)
                                    @php $nextRincianIndex = max($nextRincianIndex, $rincian->id) +1; @endphp
                                    @include('perjalanan_dinas.pegawai._rincian_biaya_item', ['index' => $rincian->id, 'rincian' => $rincian, 'existing' => true])
                                @endforeach
                            @endif

                            {{-- Loop untuk rincian baru dari old input (jika validasi gagal pada item baru) --}}
                            @if(!empty($oldRincianBiaya))
                                @foreach($oldRincianBiaya as $index => $rincian)
                                    @php $nextRincianIndex = max($nextRincianIndex, intval($index)) +1; @endphp
                                     @include('perjalanan_dinas.pegawai._rincian_biaya_item', ['index' => $index, 'rincian' => (object)$rincian, 'existing' => false])
                                @endforeach
                            @endif
                        </div>
                        <button type="button" id="add-rincian-btn" class="btn btn-outline-primary btn-sm mt-2"><i class="fas fa-plus"></i> Tambah Rincian Biaya Riil</button>

                        <div class="text-end mt-4">
                            <a href="{{ route('pegawai.laporan-perjadin.index') }}" class="btn btn-secondary">Kembali</a>
                            <button type="submit" name="submit_action" value="draft" class="btn btn-info">Simpan Draft Laporan</button>
                            <button type="submit" name="submit_action" value="serahkan" class="btn btn-success" onclick="return confirm('Apakah Anda yakin ingin menyerahkan laporan ini? Setelah diserahkan, laporan tidak dapat diubah lagi oleh Anda kecuali ada revisi.')">Serahkan Laporan untuk Verifikasi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Pastikan jQuery di-include SEBELUM script ini --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    {{-- Jika Anda menggunakan Select2 di halaman ini, uncomment scriptnya --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi rincianItemIndex berdasarkan item terakhir dari old input atau DB
        let rincianItemIndex = {{ $nextRincianIndex ?? 0 }};

        const container = document.getElementById('rincian-biaya-container');
        const addButton = document.getElementById('add-rincian-btn');

        if (addButton && container) {
            addButton.addEventListener('click', function() {
                const currentIndex = rincianItemIndex;
                const newItemHtml = `
                    <div class="rincian-item card card-body border mb-3">
                        <h6 class="text-xs font-weight-bolder">Item Biaya Baru #${currentIndex +1}</h6>
                        <div class="row">
                            <div class="col-md-5 mb-2">
                                <label for="rincian_biaya_${currentIndex}_deskripsi" class="form-label">Deskripsi Biaya <span class="text-danger">*</span></label>
                                <input type="text" name="rincian_biaya[${currentIndex}][deskripsi]" id="rincian_biaya_${currentIndex}_deskripsi" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label for="rincian_biaya_${currentIndex}_jumlah" class="form-label">Jumlah <span class="text-danger">*</span></label>
                                <input type="number" name="rincian_biaya[${currentIndex}][jumlah]" id="rincian_biaya_${currentIndex}_jumlah" class="form-control form-control-sm" required min="1" value="1">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label for="rincian_biaya_${currentIndex}_satuan" class="form-label">Satuan <span class="text-danger">*</span></label>
                                <input type="text" name="rincian_biaya[${currentIndex}][satuan]" id="rincian_biaya_${currentIndex}_satuan" class="form-control form-control-sm" required placeholder="OH, Tiket, Kali, dll">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="rincian_biaya_${currentIndex}_harga_satuan" class="form-label">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                                <input type="number" step="any" name="rincian_biaya[${currentIndex}][harga_satuan]" id="rincian_biaya_${currentIndex}_harga_satuan" class="form-control form-control-sm" required min="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5 mb-2">
                                <label for="rincian_biaya_${currentIndex}_nomor_bukti" class="form-label">Nomor Bukti</label>
                                <input type="text" name="rincian_biaya[${currentIndex}][nomor_bukti]" id="rincian_biaya_${currentIndex}_nomor_bukti" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-7 mb-2">
                                 <label for="rincian_biaya_${currentIndex}_bukti_file" class="form-label">Upload Bukti (Opsional)</label>
                                 <input type="file" name="rincian_biaya[${currentIndex}][bukti_file]" id="rincian_biaya_${currentIndex}_bukti_file" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label for="rincian_biaya_${currentIndex}_keterangan" class="form-label">Keterangan Tambahan</label>
                                <textarea name="rincian_biaya[${currentIndex}][keterangan]" id="rincian_biaya_${currentIndex}_keterangan" class="form-control form-control-sm" rows="1"></textarea>
                            </div>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm remove-rincian-btn mt-2 align-self-start" style="width: auto;">Hapus Item</button>
                    </div>
                `;
                const referenceNode = document.getElementById('add-rincian-btn');
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = newItemHtml.trim();
                container.insertBefore(tempDiv.firstElementChild, referenceNode);

                rincianItemIndex++;
            });
        }

        if (container) {
            container.addEventListener('click', function(event) {
                if (event.target && event.target.classList.contains('remove-rincian-btn')) {
                    event.target.closest('.rincian-item').remove();
                }
            });
        }
    });
    </script>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-10 col-md-12 mx-auto">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">{{ ($laporan->exists ? 'Edit' : 'Form Isian') . ' Laporan Perjalanan Dinas' }}</h6>
                        <p class="text-white text-sm ps-3 mb-0">SPT Nomor: {{ $perjalananDinas->nomor_spt ?? 'Belum Ditetapkan' }}</p>
                    </div>
                </div>
                <div class="card-body px-4 pb-3">
                    {{-- ... (Pesan Sukses, Error, Validasi Failures) ... --}}

                    {{-- Informasi Perjalanan Dinas (Readonly) --}}
                    <div class="mb-4 p-3 border rounded bg-light">
                        {{-- ... (Detail Perjalanan Dinas) ... --}}
                    </div>

                    <form action="{{ route('pegawai.laporan-perjadin.storeOrUpdate', $perjalananDinas->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        {{-- @if($laporan->exists) @method('PUT') @endif --}}

                        <h6 class="mt-2 text-sm font-weight-bold">Isian Laporan</h6>
                        <hr class="horizontal dark mt-1 mb-3">
                        {{-- ... (Field Tanggal Laporan, Ringkasan, Kendala, Saran) ... --}}

                        <h6 class="mt-4 text-sm font-weight-bold">Rincian Biaya Riil</h6>
                        <hr class="horizontal dark mt-1 mb-3">

                        @if($estimasiBiaya->isNotEmpty())
                        <div class="alert alert-light p-3 mb-3 border">
                           {{-- ... (Estimasi Biaya Awal) ... --}}
                        </div>
                        @endif

                        <div id="rincian-biaya-container">
                            @php
                                $existingRincianOld = old('existing_rincian_biaya', []);
                                $newRincianOld = old('rincian_biaya', []);
                                $phpRincianItemIndex = 0; // Indeks untuk item baru yang akan dibuat oleh JS

                                // Menentukan indeks awal untuk item baru berdasarkan item yang sudah ada (baik dari DB maupun old input)
                                if (!empty($existingRincianOld)) {
                                    $phpRincianItemIndex = count($existingRincianOld);
                                } elseif ($rincianBiayaRill->isNotEmpty() && empty($newRincianOld)) {
                                    $phpRincianItemIndex = $rincianBiayaRill->count();
                                }

                                if (!empty($newRincianOld)) {
                                     // Jika ada old input untuk item baru, tambahkan jumlahnya ke indeks
                                     // Ini lebih kompleks jika indeksnya tidak berurutan
                                     // Cara aman: selalu mulai indeks baru dari jumlah item yang sudah ada + item old baru
                                     $phpRincianItemIndex = max($phpRincianItemIndex, count($newRincianOld) + ($rincianBiayaRill->isNotEmpty() && empty($existingRincianOld) ? $rincianBiayaRill->count() : 0) );
                                }
                            @endphp

                            {{-- Loop untuk rincian biaya yang sudah ada (saat edit atau validasi gagal pada existing) --}}
                            @if(!empty($existingRincianOld))
                                @foreach($existingRincianOld as $id_rincian => $rincianData)
                                    @include('perjalanan_dinas.pegawai._rincian_biaya_item', [
                                        'index' => $id_rincian,
                                        'rincian' => (object)$rincianData,
                                        'existing' => true,
                                        'isOldInput' => true
                                    ])
                                @endforeach
                            @elseif($rincianBiayaRill->isNotEmpty() && empty($newRincianOld) && empty($existingRincianOld))
                                @foreach($rincianBiayaRill as $rincian_db)
                                    @include('perjalanan_dinas.pegawai._rincian_biaya_item', [
                                        'index' => $rincian_db->id,
                                        'rincian' => $rincian_db,
                                        'existing' => true,
                                        'isOldInput' => false
                                    ])
                                @endforeach
                            @endif

                            {{-- Loop untuk rincian baru dari old input (jika validasi gagal pada item baru) --}}
                            @if(!empty($newRincianOld))
                                @foreach($newRincianOld as $index_new_old => $rincianDataOld)
                                     @include('perjalanan_dinas.pegawai._rincian_biaya_item', [
                                         'index' => $index_new_old,
                                         'rincian' => (object)$rincianDataOld,
                                         'existing' => false,
                                         'isOldInput' => true
                                     ])
                                @endforeach
                            @endif
                        </div>
                        <button type="button" id="add-rincian-btn" class="btn btn-outline-primary btn-sm mt-2"><i class="fas fa-plus"></i> Tambah Rincian Biaya Riil</button>

                        <div class="text-end mt-4">
                            <a href="{{ route('pegawai.laporan-perjadin.index') }}" class="btn btn-secondary">Kembali</a>
                            <button type="submit" name="submit_action" value="draft" class="btn btn-info">Simpan Draft Laporan</button>
                            <button type="submit" name="submit_action" value="serahkan" class="btn btn-success" onclick="return confirm('Apakah Anda yakin ingin menyerahkan laporan ini? Setelah diserahkan, laporan tidak dapat diubah lagi oleh Anda kecuali ada revisi.')">Serahkan Laporan untuk Verifikasi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Pastikan jQuery di-include SEBELUM script ini --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    {{-- Jika Anda menggunakan Select2 di halaman ini, uncomment scriptnya --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}
    <script>
    // Pastikan script ini dijalankan setelah DOM siap, $(document).ready adalah cara jQuery
    $(document).ready(function() {
        console.log("Document ready, attempting to attach event listeners."); // Log 1

        // Inisialisasi rincianItemIndex berdasarkan item terakhir dari old input atau DB
        let rincianItemIndex = {{ $phpRincianItemIndex }}; // Ambil dari PHP yang sudah dihitung
        console.log("Initial rincianItemIndex: ", rincianItemIndex); // Log 2

        const container = $('#rincian-biaya-container'); // Gunakan jQuery selector
        const addButton = $('#add-rincian-btn');     // Gunakan jQuery selector

        if (addButton.length && container.length) { // Cek apakah elemen ditemukan
            console.log("Add button and container found."); // Log 3
            addButton.on('click', function() {
                console.log("Add rincian button clicked. Current index: ", rincianItemIndex); // Log 4
                const currentIndex = rincianItemIndex;
                const newItemHtml = `
                    <div class="rincian-item card card-body border mb-3">
                        <h6 class="text-xs font-weight-bolder">Item Biaya Baru #${currentIndex + 1}</h6>
                        <div class="row">
                            <div class="col-md-5 mb-2">
                                <label for="rincian_biaya_${currentIndex}_deskripsi" class="form-label">Deskripsi Biaya <span class="text-danger">*</span></label>
                                <input type="text" name="rincian_biaya[${currentIndex}][deskripsi]" id="rincian_biaya_${currentIndex}_deskripsi" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label for="rincian_biaya_${currentIndex}_jumlah" class="form-label">Jumlah <span class="text-danger">*</span></label>
                                <input type="number" name="rincian_biaya[${currentIndex}][jumlah]" id="rincian_biaya_${currentIndex}_jumlah" class="form-control form-control-sm" required min="1" value="1">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label for="rincian_biaya_${currentIndex}_satuan" class="form-label">Satuan <span class="text-danger">*</span></label>
                                <input type="text" name="rincian_biaya[${currentIndex}][satuan]" id="rincian_biaya_${currentIndex}_satuan" class="form-control form-control-sm" required placeholder="OH, Tiket, Kali, dll">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="rincian_biaya_${currentIndex}_harga_satuan" class="form-label">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                                <input type="number" step="any" name="rincian_biaya[${currentIndex}][harga_satuan]" id="rincian_biaya_${currentIndex}_harga_satuan" class="form-control form-control-sm" required min="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5 mb-2">
                                <label for="rincian_biaya_${currentIndex}_nomor_bukti" class="form-label">Nomor Bukti</label>
                                <input type="text" name="rincian_biaya[${currentIndex}][nomor_bukti]" id="rincian_biaya_${currentIndex}_nomor_bukti" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-7 mb-2">
                                 <label for="rincian_biaya_${currentIndex}_bukti_file" class="form-label">Upload Bukti (Opsional)</label>
                                 <input type="file" name="rincian_biaya[${currentIndex}][bukti_file]" id="rincian_biaya_${currentIndex}_bukti_file" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label for="rincian_biaya_${currentIndex}_keterangan" class="form-label">Keterangan Tambahan</label>
                                <textarea name="rincian_biaya[${currentIndex}][keterangan]" id="rincian_biaya_${currentIndex}_keterangan" class="form-control form-control-sm" rows="1"></textarea>
                            </div>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm remove-rincian-btn mt-2 align-self-start" style="width: auto;">Hapus Item</button>
                    </div>
                `;
                // Menyisipkan sebelum tombol "Tambah Rincian Biaya Riil"
                $(newItemHtml).insertBefore(addButton);

                rincianItemIndex++;
                console.log("New rincian item added. Next index: ", rincianItemIndex); // Log 5
            });
        } else {
            if (!addButton.length) console.error("Tombol #add-rincian-btn tidak ditemukan!"); // Log 6
            if (!container.length) console.error("Container #rincian-biaya-container tidak ditemukan!"); // Log 7
        }

        // Event delegation untuk tombol hapus
        container.on('click', '.remove-rincian-btn', function() {
            console.log("Remove button clicked."); // Log 8
            $(this).closest('.rincian-item').remove();
        });
    });
    </script>
@endpush