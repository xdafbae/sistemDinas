@extends('layouts.app')
@section('title', 'Edit Pengajuan Perjalanan Dinas - ' . config('app.name'))
@section('page_name', 'Edit Pengajuan: ' . ($perjalananDinas->nomor_spt ?? 'Perjalanan Dinas'))

@push('styles')
    {{-- Style Select2 sama seperti di create.blade.php --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered { padding-right: 2.5rem; padding-top: .3rem; padding-bottom: .3rem; }
        .select2-container .select2-selection--multiple, .select2-container .select2-selection--single { min-height: calc(1.5em + 1rem + 2px); border-color: #d2d6da; padding-top: 0.375rem; padding-bottom: 0.375rem; }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered { line-height: 1.5; }
        .select2-container--bootstrap-5.select2-container--focus .select2-selection, .select2-container--bootstrap-5.select2-container--open .select2-selection { border-color: #5e72e4; box-shadow: 0 0 0 0.2rem rgba(94, 114, 228, 0.25); }
        select.is-invalid + .select2-container--bootstrap-5 .select2-selection, .form-control.is-invalid + .select2-container--bootstrap-5 .select2-selection { border-color: #fd5c70 !important; }
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
                        <h6 class="text-white text-capitalize ps-3">Form Edit Pengajuan Perjalanan Dinas: {{ $perjalananDinas->nomor_spt }}</h6>
                    </div>
                </div>
                <div class="card-body px-4 pb-3">
                    @if (session('error'))
                        <div class="alert alert-danger text-white" role="alert"><strong>Error!</strong> {{ session('error') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger text-white" role="alert">
                            <strong class="d-block">Perhatian! Terdapat kesalahan input:</strong>
                            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form action="{{ route('operator.perjalanan-dinas.update', $perjalananDinas->id) }}" method="POST" id="form-perjalanan-dinas">
                        @csrf
                        @method('PUT')

                        <h6 class="mt-3 text-sm font-weight-bold">Informasi Surat Perintah Tugas (SPT)</h6>
                        <hr class="horizontal dark mt-1 mb-3">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_spt" class="form-label">Tanggal SPT <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('tanggal_spt') is-invalid @enderror" id="tanggal_spt" name="tanggal_spt" value="{{ old('tanggal_spt', $perjalananDinas->tanggal_spt->format('Y-m-d')) }}" required>
                                @error('tanggal_spt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="jenis_spt" class="form-label">Jenis Perjalanan Dinas <span class="text-danger">*</span></label>
                                <select class="form-select @error('jenis_spt') is-invalid @enderror" id="jenis_spt" name="jenis_spt" required>
                                    <option value="">Pilih Jenis Perjalanan...</option>
                                    <option value="dalam_daerah" {{ old('jenis_spt', $perjalananDinas->jenis_spt) == 'dalam_daerah' ? 'selected' : '' }}>Dalam Daerah (Kabupaten Siak > 8 Jam)</option>
                                    <option value="luar_daerah_dalam_provinsi" {{ old('jenis_spt', $perjalananDinas->jenis_spt) == 'luar_daerah_dalam_provinsi' ? 'selected' : '' }}>Luar Daerah (Dalam Provinsi Riau)</option>
                                    <option value="luar_daerah_luar_provinsi" {{ old('jenis_spt', $perjalananDinas->jenis_spt) == 'luar_daerah_luar_provinsi' ? 'selected' : '' }}>Luar Daerah (Luar Provinsi Riau)</option>
                                </select>
                                @error('jenis_spt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                             <div class="col-md-6 mb-3">
                                <label for="jenis_kegiatan" class="form-label">Jenis Kegiatan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('jenis_kegiatan') is-invalid @enderror" id="jenis_kegiatan" name="jenis_kegiatan" value="{{ old('jenis_kegiatan', $perjalananDinas->jenis_kegiatan) }}" placeholder="Misal: Biasa, Diklat, Konsultasi, Survey" required>
                                @error('jenis_kegiatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tujuan_spt" class="form-label">Tempat/Instansi Tujuan Utama <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('tujuan_spt') is-invalid @enderror" id="tujuan_spt" name="tujuan_spt" value="{{ old('tujuan_spt', $perjalananDinas->tujuan_spt) }}" placeholder="Nama tempat atau instansi yang dituju" required>
                                @error('tujuan_spt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row" id="lokasi-tujuan-sbu-group">
                            <div class="col-md-6 mb-3" id="provinsi-tujuan-group">
                                <label for="provinsi_tujuan_id" class="form-label">Provinsi Tujuan (Sesuai SBU) <span id="provinsi_required_star" class="text-danger" style="display:none;">*</span></label>
                                <select class="form-select select2-single @error('provinsi_tujuan_id') is-invalid @enderror" id="provinsi_tujuan_id" name="provinsi_tujuan_id">
                                    <option value=""></option>
                                    @foreach ($provinsis ?? [] as $provKey => $provName)
                                        <option value="{{ $provKey }}" {{ old('provinsi_tujuan_id', $perjalananDinas->provinsi_tujuan_id) == $provKey ? 'selected' : '' }}>{{ $provName }}</option>
                                    @endforeach
                                </select>
                                @error('provinsi_tujuan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3" id="kota-tujuan-group" style="display:none;">
                                <label for="kota_tujuan_id" class="form-label">Kota/Kabupaten Tujuan (Sesuai SBU)</label>
                                <input type="text" class="form-control @error('kota_tujuan_id') is-invalid @enderror" id="kota_tujuan_id" name="kota_tujuan_id" value="{{ old('kota_tujuan_id', $perjalananDinas->kota_tujuan_id) }}" placeholder="Nama kota/kab. jika SBU spesifik">
                                @error('kota_tujuan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="row" id="transportasi-internal-group" style="display:none;">
                            <div class="col-md-4 mb-3">
                                <label for="kecamatan_tujuan_id" class="form-label">Kecamatan Tujuan (di Siak)</label>
                                <input type="text" class="form-control @error('kecamatan_tujuan_id') is-invalid @enderror" id="kecamatan_tujuan_id" name="kecamatan_tujuan_id" value="{{ old('kecamatan_tujuan_id', $perjalananDinas->kecamatan_tujuan_id ?? '') }}" placeholder="Nama Kecamatan">
                                @error('kecamatan_tujuan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="desa_tujuan_id" class="form-label">Desa/Kampung Tujuan</label>
                                <input type="text" class="form-control @error('desa_tujuan_id') is-invalid @enderror" id="desa_tujuan_id" name="desa_tujuan_id" value="{{ old('desa_tujuan_id', $perjalananDinas->desa_tujuan_id ?? '') }}" placeholder="Nama Desa/Kampung">
                                @error('desa_tujuan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="jarak_km" class="form-label">Jarak (KM) jika relevan</label>
                                <input type="number" class="form-control @error('jarak_km') is-invalid @enderror" id="jarak_km" name="jarak_km" value="{{ old('jarak_km', $perjalananDinas->jarak_km ?? '') }}" placeholder="Estimasi Jarak PP">
                                @error('jarak_km') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="dasar_spt" class="form-label">Dasar SPT <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('dasar_spt') is-invalid @enderror" id="dasar_spt" name="dasar_spt" rows="3" required>{{ old('dasar_spt', $perjalananDinas->dasar_spt) }}</textarea>
                            @error('dasar_spt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="uraian_spt" class="form-label">Uraian Maksud Perjalanan Dinas <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('uraian_spt') is-invalid @enderror" id="uraian_spt" name="uraian_spt" rows="3" required>{{ old('uraian_spt', $perjalananDinas->uraian_spt) }}</textarea>
                            @error('uraian_spt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <h6 class="mt-4 text-sm font-weight-bold">Informasi Transportasi</h6>
                        <hr class="horizontal dark mt-1 mb-3">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="ya" id="opsi_transportasi_udara" name="opsi_transportasi_udara" {{ old('opsi_transportasi_udara', ($opsiUdaraTerpilih ?? false) ? 'ya' : '') == 'ya' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="opsi_transportasi_udara">Menggunakan Pesawat Udara</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="ya" id="opsi_transportasi_darat_antar_kota" name="opsi_transportasi_darat_antar_kota" {{ old('opsi_transportasi_darat_antar_kota', Str::contains(strtolower($perjalananDinas->alat_angkut ?? ''), 'antar kota') ? 'ya' : '') == 'ya' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="opsi_transportasi_darat_antar_kota">Transportasi Darat Antar Kota (SBU)</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="jumlah_taksi_di_tujuan" class="form-label">Jumlah Taksi di Tujuan (Kali)</label>
                                <input type="number" class="form-control @error('jumlah_taksi_di_tujuan') is-invalid @enderror" id="jumlah_taksi_di_tujuan" name="jumlah_taksi_di_tujuan" value="{{ old('jumlah_taksi_di_tujuan', $perjalananDinas->jumlah_taksi_di_tujuan ?? 0) }}" min="0" max="4">
                                @error('jumlah_taksi_di_tujuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                         <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="alat_angkut_lainnya" class="form-label">Detail Alat Angkut Lainnya / Catatan Transportasi</label>
                                <input type="text" class="form-control @error('alat_angkut_lainnya') is-invalid @enderror" id="alat_angkut_lainnya" name="alat_angkut_lainnya" value="{{ old('alat_angkut_lainnya', $alatAngkutLainnya ?? 'Kendaraan Dinas/Umum') }}" placeholder="Misal: Kendaraan Dinas Avanza BM XXXX XX, Travel XYZ">
                                @error('alat_angkut_lainnya') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <h6 class="mt-4 text-sm font-weight-bold">Informasi Pelaksana</h6>
                        <hr class="horizontal dark mt-1 mb-3">
                        <div class="mb-3">
                            <label for="personil_ids" class="form-label">Personil yang Berangkat <span class="text-danger">*</span></label>
                            <select class="form-select select2-multiple @error('personil_ids') is-invalid @enderror @error('personil_ids.*') is-invalid @enderror" id="personil_ids" name="personil_ids[]" multiple="multiple" required>
                                @foreach ($users ?? [] as $user)
                                    <option value="{{ $user->id }}" {{ (is_array(old('personil_ids', $selectedPersonilIds ?? [])) && in_array($user->id, old('personil_ids', $selectedPersonilIds ?? []))) ? 'selected' : '' }}>
                                        {{ $user->nama }} (NIP: {{ $user->nip ?? '-' }} | Gol: {{ $user->gol ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('personil_ids') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            @error('personil_ids.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_mulai" class="form-label">Tanggal Mulai Pelaksanaan <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $perjalananDinas->tanggal_mulai->format('Y-m-d')) }}" required>
                                @error('tanggal_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_selesai" class="form-label">Tanggal Selesai Pelaksanaan <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', $perjalananDinas->tanggal_selesai->format('Y-m-d')) }}" required>
                                @error('tanggal_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <p class="text-sm">Lama Perjalanan: <span id="lama-hari-text" class="fw-bold">0</span> hari</p>

                        <div class="text-end mt-4">
                            <a href="{{ route('operator.perjalanan-dinas.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary" id="btn-submit-pengajuan">Update Pengajuan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Script Select2 dan hitungLamaHari sama seperti di create.blade.php --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            function initSelect2(selector, placeholderText, allowClearOption = false) { $(selector).select2({ theme: "bootstrap-5", placeholder: placeholderText, allowClear: allowClearOption, width: '100%', }); }
            initSelect2('#provinsi_tujuan_id', 'Pilih Provinsi Tujuan (Sesuai SBU)...', true);
            initSelect2('#personil_ids', 'Pilih satu atau lebih personil...');

            $('#jenis_spt').on('change', function() {
                var jenis = $(this).val();
                var provinsiDropdown = $('#provinsi_tujuan_id');
                var kotaGroup = $('#kota-tujuan-group');
                var provinsiRequiredStar = $('#provinsi_required_star');
                var transportasiInternalGroup = $('#transportasi-internal-group');

                // Simpan value awal agar tidak ter-reset jika jenis_spt tidak berubah dari value awal saat load
                var initialProvinsi = "{{ old('provinsi_tujuan_id', $perjalananDinas->provinsi_tujuan_id ?? '') }}";
                var initialKota = "{{ old('kota_tujuan_id', $perjalananDinas->kota_tujuan_id ?? '') }}";
                var initialKecamatan = "{{ old('kecamatan_tujuan_id', $perjalananDinas->kecamatan_tujuan_id ?? '') }}";
                var initialDesa = "{{ old('desa_tujuan_id', $perjalananDinas->desa_tujuan_id ?? '') }}";
                var initialJarak = "{{ old('jarak_km', $perjalananDinas->jarak_km ?? '') }}";


                // Reset field hanya jika jenis SPT berubah dari yang sudah ada di DB (untuk edit)
                // atau jika ini adalah form create (di mana $perjalananDinas->jenis_spt tidak ada)
                var jenisAwal = "{{ $perjalananDinas->jenis_spt ?? '' }}";
                if (jenis !== jenisAwal || jenisAwal === '') {
                    provinsiDropdown.val(null).trigger('change');
                    $('#kota_tujuan_id').val('');
                     $('#kecamatan_tujuan_id').val('');
                    $('#desa_tujuan_id').val('');
                    $('#jarak_km').val('');
                } else {
                    // Kembalikan nilai awal jika jenis_spt tidak berubah
                    provinsiDropdown.val(initialProvinsi).trigger('change');
                    $('#kota_tujuan_id').val(initialKota);
                    $('#kecamatan_tujuan_id').val(initialKecamatan);
                    $('#desa_tujuan_id').val(initialDesa);
                    $('#jarak_km').val(initialJarak);
                }


                provinsiDropdown.prop('disabled', false);
                kotaGroup.hide();
                provinsiRequiredStar.hide();
                transportasiInternalGroup.hide();


                if (jenis === 'dalam_daerah') {
                    provinsiDropdown.val('RIAU').trigger('change').prop('disabled', true);
                    transportasiInternalGroup.show();
                    $('#kota_tujuan_id').val('SIAK'); // Default untuk dalam daerah Kab. Siak
                    // kotaGroup.show().find('input').prop('disabled', true); // Kota bisa di-set Siak dan disable
                } else if (jenis === 'luar_daerah_dalam_provinsi') {
                    provinsiDropdown.val('RIAU').trigger('change').prop('disabled', true);
                    kotaGroup.show().find('input').prop('disabled', false);
                } else if (jenis === 'luar_daerah_luar_provinsi') {
                    provinsiRequiredStar.show();
                    kotaGroup.show().find('input').prop('disabled', false);
                }
            }).trigger('change');

             function hitungLamaHari() {
                var tglMulaiVal = $('#tanggal_mulai').val();
                var tglSelesaiVal = $('#tanggal_selesai').val();
                var tglSelesaiEl = $('#tanggal_selesai')[0];

                if (tglMulaiVal && tglSelesaiVal) {
                    var mulai = new Date(tglMulaiVal);
                    var selesai = new Date(tglSelesaiVal);

                    if (selesai >= mulai) {
                        var diffTime = Math.abs(selesai - mulai);
                        var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                        $('#lama-hari-text').text(diffDays);
                        tglSelesaiEl.setCustomValidity("");
                    } else {
                        $('#lama-hari-text').text('INVALID');
                         tglSelesaiEl.setCustomValidity("Tanggal selesai tidak boleh sebelum tanggal mulai.");
                    }
                } else {
                    $('#lama-hari-text').text('0');
                    tglSelesaiEl.setCustomValidity("");
                }
            }
            $('#tanggal_mulai, #tanggal_selesai').on('change input', hitungLamaHari);
            hitungLamaHari();
        });
    </script>
@endpush