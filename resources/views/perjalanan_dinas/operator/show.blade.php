@extends('layouts.app')
@section('title', 'Detail Pengajuan Perjalanan Dinas - ' . config('app.name'))
@section('page_name', 'Detail Pengajuan: ' . $perjalananDinas->nomor_spt)

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-10 col-md-12 mx-auto">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Detail Pengajuan Perjalanan Dinas</h6>
                    </div>
                </div>
                <div class="card-body px-4 pb-3">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong class="text-dark">Nomor SPT:</strong>
                            <p>{{ $perjalananDinas->nomor_spt }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-dark">Tanggal SPT:</strong>
                            <p>{{ $perjalananDinas->tanggal_spt->translatedFormat('d F Y') }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong class="text-dark">Jenis Perjalanan Dinas:</strong>
                            <p>{{ ucfirst(str_replace('_', ' ', $perjalananDinas->jenis_spt)) }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-dark">Jenis Kegiatan:</strong>
                            <p>{{ $perjalananDinas->jenis_kegiatan }}</p>
                        </div>
                    </div>
                     <div class="row mb-3">
                        <div class="col-md-6">
                            <strong class="text-dark">Tempat/Instansi Tujuan Utama:</strong>
                            <p>{{ $perjalananDinas->tujuan_spt }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong class="text-dark">Lokasi Tujuan (SBU):</strong>
                            <p>
                                @if($perjalananDinas->provinsi_tujuan_id)
                                    Provinsi: {{ $perjalananDinas->provinsi_tujuan_id }}
                                @endif
                                @if($perjalananDinas->kota_tujuan_id)
                                    , Kota/Kab: {{ $perjalananDinas->kota_tujuan_id }}
                                @endif
                                {{-- Tampilkan kecamatan/desa jika ada dan relevan --}}
                            </p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong class="text-dark">Dasar SPT:</strong>
                        <div class="p-2 border rounded" style="white-space: pre-wrap;">{!! nl2br(e($perjalananDinas->dasar_spt)) !!}</div>
                    </div>
                    <div class="mb-3">
                        <strong class="text-dark">Uraian Maksud Perjalanan Dinas:</strong>
                        <div class="p-2 border rounded" style="white-space: pre-wrap;">{!! nl2br(e($perjalananDinas->uraian_spt)) !!}</div>
                    </div>
                    <div class="mb-3">
                        <strong class="text-dark">Alat Angkut:</strong>
                        <p>{{ $perjalananDinas->alat_angkut ?? '-' }}</p>
                    </div>

                    <h6 class="mt-4 text-sm font-weight-bold">Informasi Pelaksana</h6>
                    <hr class="horizontal dark mt-1 mb-2">
                    @foreach($perjalananDinas->personil as $personil)
                        <div class="mb-2 p-2 border-bottom">
                            <strong>{{ $loop->iteration }}. {{ $personil->nama }}</strong> (NIP: {{ $personil->nip ?? '-' }})<br>
                            <small>Gol: {{ $personil->gol ?? '-' }} - Jabatan: {{ $personil->jabatan ?? '-' }}</small>
                        </div>
                    @endforeach

                    <div class="row mt-3 mb-3">
                        <div class="col-md-4">
                            <strong class="text-dark">Tanggal Mulai:</strong>
                            <p>{{ $perjalananDinas->tanggal_mulai->translatedFormat('d F Y') }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong class="text-dark">Tanggal Selesai:</strong>
                            <p>{{ $perjalananDinas->tanggal_selesai->translatedFormat('d F Y') }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong class="text-dark">Lama Hari:</strong>
                            <p>{{ $perjalananDinas->lama_hari }} hari</p>
                        </div>
                    </div>

                    <h6 class="mt-4 text-sm font-weight-bold">Estimasi Biaya</h6>
                    <hr class="horizontal dark mt-1 mb-2">
                    @if($perjalananDinas->biayaDetails->isNotEmpty())
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Deskripsi Biaya</th>
                                    <th>Personil Terkait</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-center">Jml. Unit/Hari</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($perjalananDinas->biayaDetails as $biaya)
                                <tr>
                                    <td>{{ $biaya->deskripsi_biaya }}</td>
                                    <td>{{ $biaya->userTerkait->nama ?? 'Semua' }}</td>
                                    <td class="text-end">Rp {{ number_format($biaya->harga_satuan, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        {{ $biaya->jumlah_unit > 0 ? $biaya->jumlah_unit . ' ' . ($biaya->sbuItem->satuan ?? '') : '-' }}
                                        @if ($biaya->jumlah_hari_terkait > 0 && $biaya->jumlah_hari_terkait != $biaya->jumlah_unit)
                                         x {{ $biaya->jumlah_hari_terkait }} hari
                                        @endif
                                    </td>
                                    <td class="text-end">Rp {{ number_format($biaya->subtotal_biaya, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total Estimasi Biaya</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($perjalananDinas->total_estimasi_biaya, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">Detail estimasi biaya belum tersedia.</p>
                    @endif

                    <h6 class="mt-4 text-sm font-weight-bold">Status & Catatan</h6>
                    <hr class="horizontal dark mt-1 mb-2">
                    <p><strong class="text-dark">Status Saat Ini:</strong> <span class="badge bg-gradient-info">{{ ucfirst(str_replace('_', ' ', $perjalananDinas->status)) }}</span></p>
                    @if($perjalananDinas->catatan_verifikator)
                        <p><strong class="text-dark">Catatan Verifikator:</strong></p>
                        <div class="p-2 border rounded bg-light" style="white-space: pre-wrap;">{!! nl2br(e($perjalananDinas->catatan_verifikator)) !!}</div>
                    @endif
                     @if($perjalananDinas->catatan_atasan)
                        <p class="mt-2"><strong class="text-dark">Catatan Atasan:</strong></p>
                        <div class="p-2 border rounded bg-light" style="white-space: pre-wrap;">{!! nl2br(e($perjalananDinas->catatan_atasan)) !!}</div>
                    @endif


                    <div class="text-center mt-4">
                        <a href="{{ route('operator.perjalanan-dinas.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
                        @if(in_array($perjalananDinas->status, ['draft', 'revisi_operator_verifikator', 'revisi_operator_atasan']))
                            <a href="{{ route('operator.perjalanan-dinas.edit', $perjalananDinas->id) }}" class="btn btn-info">Edit Pengajuan</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Script Select2 dan hitungLamaHari sama seperti di create.blade.php --}}
    {{-- Hanya saja, untuk edit, pastikan nilai awal Select2 dan field lain terisi dari $perjalananDinas --}}
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // JavaScript untuk show page biasanya tidak memerlukan interaksi form sebanyak create/edit
        // Jika ada Select2 di sini (misalnya untuk filter atau tampilan lain), inisialisasikan.
        // Logika hitungLamaHari dan jenis_spt change handler mungkin tidak diperlukan di show page.
    </script>
@endpush