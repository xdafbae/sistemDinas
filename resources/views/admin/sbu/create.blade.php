@extends('layouts.app') {{-- Sesuaikan dengan layout Argon Anda --}}

@section('title', 'Tambah Item SBU - ' . config('app.name'))
@section('page_name', 'Tambah Item SBU Baru')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-10 col-md-12 mx-auto">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Form Tambah Item SBU</h6>
                    </div>
                </div>
                <div class="card-body px-4 pb-3">
                    @if ($errors->any())
                        <div class="alert alert-danger text-white" role="alert">
                            <strong class="d-block">Perhatian! Terdapat kesalahan input:</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.sbu.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="kategori_biaya" class="form-label">Kategori Biaya <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('kategori_biaya') is-invalid @enderror" id="kategori_biaya" name="kategori_biaya" value="{{ old('kategori_biaya') }}" required placeholder="Contoh: UANG_HARIAN, PENGINAPAN">
                                @error('kategori_biaya') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-text text-muted">Gunakan underscore untuk spasi, misal: TRANSPORTASI_UDARA</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="uraian_biaya" class="form-label">Uraian Biaya <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('uraian_biaya') is-invalid @enderror" id="uraian_biaya" name="uraian_biaya" value="{{ old('uraian_biaya') }}" required placeholder="Misal: Luar Kabupaten Aceh, Hotel Gol IV Riau">
                                @error('uraian_biaya') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="provinsi_tujuan" class="form-label">Provinsi Tujuan</label>
                                <input type="text" class="form-control @error('provinsi_tujuan') is-invalid @enderror" id="provinsi_tujuan" name="provinsi_tujuan" value="{{ old('provinsi_tujuan') }}" placeholder="Nama Provinsi (jika berlaku)">
                                @error('provinsi_tujuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="kota_tujuan" class="form-label">Kota/Kabupaten Tujuan</label>
                                <input type="text" class="form-control @error('kota_tujuan') is-invalid @enderror" id="kota_tujuan" name="kota_tujuan" value="{{ old('kota_tujuan') }}" placeholder="Nama Kota/Kab (jika berlaku)">
                                @error('kota_tujuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="kecamatan_tujuan" class="form-label">Kecamatan Tujuan</label>
                                <input type="text" class="form-control @error('kecamatan_tujuan') is-invalid @enderror" id="kecamatan_tujuan" name="kecamatan_tujuan" value="{{ old('kecamatan_tujuan') }}" placeholder="Nama Kecamatan (jika berlaku)">
                                @error('kecamatan_tujuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                         <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="desa_tujuan" class="form-label">Desa/Kampung Tujuan</label>
                                <input type="text" class="form-control @error('desa_tujuan') is-invalid @enderror" id="desa_tujuan" name="desa_tujuan" value="{{ old('desa_tujuan') }}" placeholder="Nama Desa/Kampung (jika berlaku)">
                                @error('desa_tujuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="satuan" class="form-label">Satuan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('satuan') is-invalid @enderror" id="satuan" name="satuan" value="{{ old('satuan') }}" required placeholder="Misal: OH, Kali, Tiket, PP, KM">
                                @error('satuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="besaran_biaya" class="form-label">Besaran Biaya (Rp) <span class="text-danger">*</span></label>
                                <input type="number" step="any" class="form-control @error('besaran_biaya') is-invalid @enderror" id="besaran_biaya" name="besaran_biaya" value="{{ old('besaran_biaya') }}" required placeholder="Contoh: 150000">
                                @error('besaran_biaya') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tipe_perjalanan" class="form-label">Tipe Perjalanan</label>
                                <input type="text" class="form-control @error('tipe_perjalanan') is-invalid @enderror" id="tipe_perjalanan" name="tipe_perjalanan" value="{{ old('tipe_perjalanan') }}" placeholder="Misal: LUAR_DAERAH_LUAR_KABUPATEN, DIKLAT">
                                @error('tipe_perjalanan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-text text-muted">Contoh: DALAM_KABUPATEN_LEBIH_8_JAM, LUAR_DAERAH_LUAR_KABUPATEN, DIKLAT, Semua</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tingkat_pejabat_atau_golongan" class="form-label">Tingkat Pejabat/Golongan</label>
                                <input type="text" class="form-control @error('tingkat_pejabat_atau_golongan') is-invalid @enderror" id="tingkat_pejabat_atau_golongan" name="tingkat_pejabat_atau_golongan" value="{{ old('tingkat_pejabat_atau_golongan') }}" placeholder="Misal: ESELON_II, GOL_IV, Semua">
                                @error('tingkat_pejabat_atau_golongan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="form-text text-muted">Contoh: KEPALA_DAERAH_ESELON_I, ESELON_II, ESELON_III_GOL_IV, GOL_II_I_NON_ASN, Semua</small>
                            </div>
                        </div>
                         <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="jarak_km_min" class="form-label">Jarak KM Minimal</label>
                                <input type="number" class="form-control @error('jarak_km_min') is-invalid @enderror" id="jarak_km_min" name="jarak_km_min" value="{{ old('jarak_km_min') }}" placeholder="Angka (jika berdasarkan jarak)">
                                @error('jarak_km_min') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="jarak_km_max" class="form-label">Jarak KM Maksimal</label>
                                <input type="number" class="form-control @error('jarak_km_max') is-invalid @enderror" id="jarak_km_max" name="jarak_km_max" value="{{ old('jarak_km_max') }}" placeholder="Angka (jika berdasarkan jarak)">
                                @error('jarak_km_max') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan Tambahan</label>
                            <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" rows="3">{{ old('keterangan') }}</textarea>
                            @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="text-end">
                            <a href="{{ route('admin.sbu.index') }}" class="btn btn-secondary mt-4">Batal</a>
                            <button type="submit" class="btn btn-primary mt-4">Simpan Item SBU</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection