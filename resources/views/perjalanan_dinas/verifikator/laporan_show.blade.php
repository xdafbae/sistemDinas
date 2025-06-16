@extends('layouts.app')
@section('title', 'Verifikasi Laporan Perjadin - SPT No: ' . Str::limit($laporan->perjalananDinas->nomor_spt ?? 'N/A',
    20))
@section('page_name', 'Verifikasi Laporan: ' . ($laporan->perjalananDinas->nomor_spt ?? 'N/A'))

@push('styles')
    <style>
        .table-sm th,
        .table-sm td {
            padding: 0.4rem;
            /* Padding lebih kecil untuk tabel detail */
        }

        .detail-section p {
            margin-bottom: 0.5rem;
        }

        .detail-section strong {
            color: #32325d;
            /* Warna teks label sedikit lebih gelap */
        }

        .badge-lg {
            /* Sedikit lebih besar dari badge default */
            padding: .5em .7em;
            font-size: .875rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-10 col-md-12 mx-auto">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
                            <h6 class="text-white text-capitalize ps-3">Verifikasi Laporan Perjalanan Dinas</h6>
                            <p class="text-white text-sm ps-3 mb-0">Pelapor: {{ $laporan->pelapor->nama ?? '-' }} | SPT:
                                {{ $laporan->perjalananDinas->nomor_spt ?? 'Belum Ditetapkan' }}</p>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-3">
                        @if (session('success'))
                            <div class="alert alert-success text-white" role="alert">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger text-white" role="alert"><strong>Error!</strong>
                                {{ session('error') }}</div>
                        @endif
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

                        {{-- Detail Perjalanan Dinas --}}
                        <div class="detail-section mb-4">
                            <h6 class="text-dark font-weight-bolder text-sm">Detail Perjalanan Dinas</h6>
                            <hr class="horizontal dark mt-1 mb-2">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <strong class="text-xs">Nomor SPT:</strong>
                                    <p class="text-sm">{{ $laporan->perjalananDinas->nomor_spt ?? 'Belum Ditetapkan' }}</p>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong class="text-xs">Tanggal SPT:</strong>
                                    <p class="text-sm">
                                        {{ $laporan->perjalananDinas->tanggal_spt ? $laporan->perjalananDinas->tanggal_spt->translatedFormat('d F Y') : '-' }}
                                    </p>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <strong class="text-xs">Tujuan Utama:</strong>
                                    <p class="text-sm">{{ $laporan->perjalananDinas->tujuan_spt }}
                                        @if ($laporan->perjalananDinas->kota_tujuan_id || $laporan->perjalananDinas->provinsi_tujuan_id)
                                            ({{ $laporan->perjalananDinas->kota_tujuan_id ?? '' }}{{ $laporan->perjalananDinas->kota_tujuan_id && $laporan->perjalananDinas->provinsi_tujuan_id ? ', ' : '' }}{{ $laporan->perjalananDinas->provinsi_tujuan_id ?? '' }})
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong class="text-xs">Tanggal Pelaksanaan:</strong>
                                    <p class="text-sm">
                                        {{ $laporan->perjalananDinas->tanggal_mulai->translatedFormat('d F Y') }} s/d
                                        {{ $laporan->perjalananDinas->tanggal_selesai->translatedFormat('d F Y') }}</p>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong class="text-xs">Lama Perjalanan:</strong>
                                    <p class="text-sm">{{ $laporan->perjalananDinas->lama_hari }} hari</p>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <strong class="text-xs">Personil yang Ditugaskan:</strong>
                                    <p class="text-sm">
                                        {{ $laporan->perjalananDinas->personil->pluck('nama')->implode(', ') }}</p>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong class="text-xs">Diajukan Oleh (Operator):</strong>
                                    <p class="text-sm">{{ $laporan->perjalananDinas->operator->nama ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Hasil Laporan Pegawai --}}
                        <div class="detail-section mb-4">
                            <h6 class="text-dark font-weight-bolder text-sm">Hasil Laporan Pegawai</h6>
                            <hr class="horizontal dark mt-1 mb-2">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <strong class="text-xs">Pelapor:</strong>
                                    <p class="text-sm">{{ $laporan->pelapor->nama ?? '-' }}</p>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <strong class="text-xs">Tanggal Laporan:</strong>
                                    <p class="text-sm">
                                        {{ $laporan->tanggal_laporan ? $laporan->tanggal_laporan->translatedFormat('d F Y') : '-' }}
                                    </p>
                                </div>
                            </div>
                            <div class="mb-2">
                                <strong class="text-xs">Ringkasan Hasil Kegiatan:</strong>
                                <div class="p-2 border rounded bg-light text-sm" style="white-space: pre-wrap;">
                                    {!! nl2br(e($laporan->ringkasan_hasil_kegiatan)) !!}</div>
                            </div>
                            @if ($laporan->kendala_dihadapi)
                                <div class="mb-2">
                                    <strong class="text-xs">Kendala yang Dihadapi:</strong>
                                    <div class="p-2 border rounded bg-light text-sm" style="white-space: pre-wrap;">
                                        {!! nl2br(e($laporan->kendala_dihadapi)) !!}</div>
                                </div>
                            @endif
                            @if ($laporan->saran_tindak_lanjut)
                                <div class="mb-2">
                                    <strong class="text-xs">Saran / Tindak Lanjut:</strong>
                                    <div class="p-2 border rounded bg-light text-sm" style="white-space: pre-wrap;">
                                        {!! nl2br(e($laporan->saran_tindak_lanjut)) !!}</div>
                                </div>
                            @endif
                        </div>


                        {{-- Rincian Biaya Riil Dilaporkan --}}
                        <div class="detail-section mb-4">
                            <h6 class="text-dark font-weight-bolder text-sm">Rincian Biaya Riil Dilaporkan</h6>
                            <hr class="horizontal dark mt-1 mb-2">
                            @if ($laporan->rincianBiaya->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-items-center mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Deskripsi Biaya</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                                    Jumlah</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Satuan</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">
                                                    Harga Satuan (Rp)</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">
                                                    Subtotal (Rp)</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    No. Bukti</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                                    File Bukti</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($laporan->rincianBiaya as $rincian)
                                                <tr>
                                                    <td class="text-xs">{{ $rincian->deskripsi_biaya_rill }}</td>
                                                    <td class="text-xs text-center">{{ $rincian->jumlah_rill }}</td>
                                                    <td class="text-xs">{{ $rincian->satuan_rill }}</td>
                                                    <td class="text-xs text-end">
                                                        {{ number_format($rincian->harga_satuan_rill, 0, ',', '.') }}</td>
                                                    <td class="text-xs text-end">
                                                        {{ number_format($rincian->subtotal_biaya_rill, 0, ',', '.') }}
                                                    </td>
                                                    <td class="text-xs">{{ $rincian->nomor_bukti ?? '-' }}</td>
                                                    <td class="text-xs text-center">
                                                        @if ($rincian->path_bukti_file)
                                                            <a href="{{ Storage::url($rincian->path_bukti_file) }}"
                                                                target="_blank"
                                                                class="btn btn-link text-info p-0 m-0 text-xs">
                                                                <i class="fas fa-file-alt"></i> Lihat
                                                            </a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="text-xs">
                                                        {{ Str::limit($rincian->keterangan_rill, 25) ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="fw-bold">
                                                <td colspan="4" class="text-end text-uppercase">Total Biaya Riil
                                                    Dilaporkan:</td>
                                                <td class="text-end">Rp
                                                    {{ number_format($laporan->total_biaya_rill_dilaporkan, 0, ',', '.') }}
                                                </td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-muted">Tidak ada rincian biaya riil yang dilaporkan.</p>
                            @endif
                        </div>

                        {{-- Referensi Estimasi Biaya Awal --}}
                        @if ($estimasiBiaya->isNotEmpty())
                            <div class="detail-section mb-4">
                                <h6 class="text-dark font-weight-bolder text-sm">Referensi Estimasi Biaya Awal</h6>
                                <hr class="horizontal dark mt-1 mb-2">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Deskripsi Biaya (Estimasi)</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Personil Terkait</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">
                                                    Subtotal (Rp)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($estimasiBiaya as $est)
                                                <tr>
                                                    <td class="text-xs">{{ $est->deskripsi_biaya }}</td>
                                                    <td class="text-xs">{{ $est->userTerkait->nama ?? 'Semua' }}</td>
                                                    <td class="text-xs text-end">
                                                        {{ number_format($est->subtotal_biaya, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                            <tr class="fw-bold">
                                                <td colspan="2" class="text-end text-uppercase">Total Estimasi Awal:
                                                </td>
                                                <td class="text-end">Rp
                                                    {{ number_format($laporan->perjalananDinas->total_estimasi_biaya, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <hr class="horizontal dark my-4">
                        <h5 class="mt-3 text-dark font-weight-bold">Form Verifikasi Laporan</h5>
                        <form action="{{ route('verifikator.laporan-perjadin.process', $laporan->id) }}" method="POST">
                            @csrf
                            {{-- Opsional: Input untuk verifikator/bendahara menyesuaikan total biaya riil jika ada kesalahan hitung oleh pegawai --}}
                            {{-- <div class="mb-3">
                            <label for="total_biaya_rill_disetujui" class="form-label">Total Biaya Riil Disetujui/Diverifikasi (Rp)</label>
                            <input type="number" step="any" class="form-control @error('total_biaya_rill_disetujui') is-invalid @enderror"
                                   id="total_biaya_rill_disetujui" name="total_biaya_rill_disetujui"
                                   value="{{ old('total_biaya_rill_disetujui', $laporan->total_biaya_rill_dilaporkan) }}" required>
                            @error('total_biaya_rill_disetujui') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div> --}}

                            <div class="mb-3">
                                <label for="catatan_pereview" class="form-label">Catatan Verifikator (Wajib diisi jika
                                    Revisi)</label>
                                <textarea class="form-control @error('catatan_pereview') is-invalid @enderror" id="catatan_pereview"
                                    name="catatan_pereview" rows="3">{{ old('catatan_pereview') }}</textarea>
                                @error('catatan_pereview')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" name="aksi_verifikasi_laporan" value="revisi"
                                    class="btn btn-warning me-2">Kembalikan untuk Revisi</button>
                                <button type="submit" name="aksi_verifikasi_laporan" value="setuju"
                                    class="btn btn-success">Setujui Laporan</button>
                            </div>
                        </form>
                        <div class="mt-4 text-start"> {{-- Tombol kembali di bawah form --}}
                            <a href="{{ route('verifikator.laporan-perjadin.index') }}"
                                class="btn btn-outline-secondary btn-sm">Kembali ke Daftar Verifikasi</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
