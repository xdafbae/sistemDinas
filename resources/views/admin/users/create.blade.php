@extends('layouts.app')
@section('title', 'Tambah User Baru - ' . config('app.name'))
@section('page_name', 'Tambah User Baru')

@push('styles')
    {{-- CSS Select2 dari CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    {{-- Opsional: Tema Bootstrap untuk Select2 jika Anda ingin tampilan yang lebih menyatu dengan Bootstrap --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        /* Kustomisasi kecil untuk Select2 agar lebih pas dengan form Argon */
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
            padding-right: 2.5rem; /* Sesuaikan agar ikon clear tidak terpotong */
        }
        .select2-container .select2-selection--multiple {
            min-height: calc(1.5em + 1rem + 2px); /* Samakan tinggi dengan input form-control-lg Argon */
            border-color: #d2d6da; /* Warna border default Argon */
        }
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: #5e72e4; /* Warna border focus Argon */
            box-shadow: 0 0 0 0.2rem rgba(94, 114, 228, 0.25);
        }
        .is-invalid .select2-container--bootstrap-5 .select2-selection {
             border-color: #fd5c70 !important; /* Warna border error Argon */
        }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-10 col-md-12 mx-auto">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Form Tambah User Baru</h6>
                    </div>
                </div>
                <div class="card-body px-4 pb-3">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf

                        {{-- ... field nama, email, nip, nomor_hp, jabatan, gol ... --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input class="form-control @error('nama') is-invalid @enderror" type="text" name="nama" id="nama" value="{{ old('nama') }}" required>
                                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" id="email" value="{{ old('email') }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nip" class="form-label">NIP</label>
                                <input class="form-control @error('nip') is-invalid @enderror" type="text" name="nip" id="nip" value="{{ old('nip') }}">
                                @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nomor_hp" class="form-label">Nomor HP</label>
                                <input class="form-control @error('nomor_hp') is-invalid @enderror" type="text" name="nomor_hp" id="nomor_hp" value="{{ old('nomor_hp') }}" placeholder="Contoh: 081234567890">
                                @error('nomor_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="jabatan" class="form-label">Jabatan</label>
                                <input class="form-control @error('jabatan') is-invalid @enderror" type="text" name="jabatan" id="jabatan" value="{{ old('jabatan') }}">
                                @error('jabatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gol" class="form-label">Golongan</label>
                                <input class="form-control @error('gol') is-invalid @enderror" type="text" name="gol" id="gol" value="{{ old('gol') }}">
                                @error('gol') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="roles" class="form-label">Roles</label>
                                {{-- Class 'form-control' ditambahkan agar Select2 mengambil beberapa style default,
                                     namun tema Select2 yang akan lebih dominan.
                                     Hapus atribut 'size' karena Select2 akan mengaturnya. --}}
                                <select name="roles[]" id="roles-select" class="form-select select2-multiple @error('roles') is-invalid @enderror @error('roles.*') is-invalid @enderror" multiple="multiple">
                                    @foreach ($roles ?? [] as $id => $name)
                                        <option value="{{ $id }}" {{ (is_array(old('roles')) && in_array($id, old('roles', []))) ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('roles') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                @error('roles.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="horizontal dark my-3">
                        <p class="text-uppercase text-sm">Password</p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input class="form-control @error('password') is-invalid @enderror" type="password" name="password" id="password" required>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                <input class="form-control" type="password" name="password_confirmation" id="password_confirmation" required>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- jQuery (jika belum ada di layout utama Anda) --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    {{-- JS Select2 dari CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inisialisasi Select2
            $('#roles-select').select2({
                theme: "bootstrap-5", // Gunakan tema Bootstrap 5 untuk Select2
                placeholder: "Pilih satu atau lebih role",
                allowClear: true, // Menambahkan tombol clear (x)
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style', // Agar width responsif
            });
        });
    </script>
@endpush