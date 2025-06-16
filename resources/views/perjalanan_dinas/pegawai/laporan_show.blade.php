@extends('layouts.app')
@section('title', 'Detail Laporan Perjadin - SPT No: ' . Str::limit($laporan->perjalananDinas->nomor_spt ?? 'N/A', 20))
@section('page_name', 'Detail Laporan Perjalanan Dinas')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-10 col-md-12 mx-auto">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Detail Laporan Perjalanan Dinas</h6>
                        <p class="text-white text-sm ps-3 mb-0">SPT Nomor: {{ $laporan->perjalananDinas->nomor_spt ?? 'Belum Ditetapkan' }}</p>
                    </div>
                </div>
                <div class="card-body px-4 pb-3">
                    @if (session('success'))
                        <div class="alert alert-success text-white" role="alert">{{ session('success') }}</div>
                    @endif
                    @if (session('info'))
                        <div class="alert alert-info text-white" role="alert">{{ session('info') }}</div>
                    @endif

                    <h6 class="text-dark font-weight-bolder text-sm mt-3">Informasi Perjalanan Dinas</h6>
                    <hr class="horizontal dark mt-1 mb-2">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong class="text-xs">Tujuan:</strong>
                            <p class="text-sm">{{ $laporan->perjalananDinas->tujuan_spt }}
                                @if($laporan->perjalananDinas->kota_tujuan_id || $laporan->perjalananDinas->provinsi_tujuan_id)
                                    ({{ $laporan->perjalananDinas->kota_tujuan_id ?? '' }}{{ $laporan->perjalananDinas->kota_tujuan_id && $laporan->perjalananDinas->provinsi_tujuan_id ? ', ' : '' }}{{ $laporan->perjalananDinas->provinsi_tujuan_id ?? '' }})
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong class="text-xs">Tanggal Pelaksanaan:</strong>
                            <p class="text-sm">{{ $laporan->perjalananDinas->tanggal_mulai->translatedFormat('d M Y') }} s/d {{ $laporan->perjalananDinas->tanggal_selesai->translatedFormat('d M Y') }}</p>
                        </div>
                        <div class="col-md-12 mb-2">
                            <strong class="text-xs">Personil:</strong>
                            <p class="text-sm">{{ $laporan->perjalananDinas->personil->pluck('nama')->implode(', ') }}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong class="text-xs">Pelapor:</strong>
                            <p class="text-sm">{{ $laporan->user->nama ?? '-' }}</p>
                        </div>
                         <div class="col-md-6 mb-2">
                            <strong class="text-xs">Status Laporan:</strong>
                            <p class="text-sm">
                                @php
                                    $statusText = ucfirst(str_replace('_', ' ', $laporan->status_laporan));
                                    $badgeClass = 'bg-secondary';
                                    if($laporan->status_laporan == 'draft') $badgeClass = 'bg-light text-dark border';
                                    if($laporan->status_laporan == 'diserahkan_untuk_verifikasi') $badgeClass = 'bg-info';
                                    if($laporan->status_laporan == 'revisi_laporan') $badgeClass = 'bg-warning';
                                    if($laporan->status_laporan == 'disetujui_bendahara' || $laporan->status_laporan == 'selesai_diproses') $badgeClass = 'bg-success';
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
                            </p>
                        </div>
                    </div>


                    <h6 class="text-dark font-weight-bolder text-sm mt-4">Detail Laporan</h6>
                    <hr class="horizontal dark mt-1 mb-2">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong class="text-xs">Tanggal Laporan:</strong>
                            <p class="text-sm">{{ $laporan->tanggal_laporan ? $laporan->tanggal_laporan->translatedFormat('d F Y') : '-' }}</p>
                        </div>
                    </div>
                    <div class="mb-2">
                        <strong class="text-xs">Ringkasan Hasil Kegiatan:</strong>
                        <div class="p-2 border rounded bg-light text-sm" style="white-space: pre-wrap;">{!! nl2br(e($laporan->ringkasan_hasil_kegiatan)) !!}</div>
                    </div>
                    @if($laporan->kendala_dihadapi)
                    <div class="mb-2">
                        <strong class="text-xs">Kendala yang Dihadapi:</strong>
                        <div class="p-2 border rounded bg-light text-sm" style="white-space: pre-wrap;">{!! nl2br(e($laporan->kendala_dihadapi)) !!}</div>
                    </div>
                    @endif
                    @if($laporan->saran_tindak_lanjut)
                    <div class="mb-2">
                        <strong class="text-xs">Saran / Tindak Lanjut:</strong>
                        <div class="p-2 border rounded bg-light text-sm" style="white-space: pre-wrap;">{!! nl2br(e($laporan->saran_tindak_lanjut)) !!}</div>
                    </div>
                    @endif

                    <h6 class="text-dark font-weight-bolder text-sm mt-4">Rincian Biaya Riil Dilaporkan</h6>
                    <hr class="horizontal dark mt-1 mb-2">
                    @if($laporan->rincianBiaya->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-items-center">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-xxs">Deskripsi Biaya</th>
                                    <th class="text-xxs text-center">Jumlah</th>
                                    <th class="text-xxs">Satuan</th>
                                    <th class="text-xxs text-end">Harga Satuan (Rp)</th>
                                    <th class="text-xxs text-end">Subtotal (Rp)</th>
                                    <th class="text-xxs">No. Bukti</th>
                                    <th class="text-xxs">File Bukti</th>
                                    <th class="text-xxs">Ket.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($laporan->rincianBiaya as $rincian)
                                <tr>
                                    <td class="text-xs">{{ $rincian->deskripsi_biaya_rill }}</td>
                                    <td class="text-xs text-center">{{ $rincian->jumlah_rill }}</td>
                                    <td class="text-xs">{{ $rincian->satuan_rill }}</td>
                                    <td class="text-xs text-end">{{ number_format($rincian->harga_satuan_rill, 0, ',', '.') }}</td>
                                    <td class="text-xs text-end">{{ number_format($rincian->subtotal_biaya_rill, 0, ',', '.') }}</td>
                                    <td class="text-xs">{{ $rincian->nomor_bukti ?? '-' }}</td>
                                    <td class="text-xs">
                                        @if($rincian->path_bukti_file)
                                            <a href="{{ Storage::url($rincian->path_bukti_file) }}" target="_blank" class="btn btn-link text-info p-0 m-0">
                                                <i class="fas fa-file-alt"></i> Lihat
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-xs">{{ Str::limit($rincian->keterangan_rill, 20) ?? '-' }}</td>
                                </tr>
                                @endforeach
                                <tr class="fw-bold">
                                    <td colspan="4" class="text-end">Total Biaya Riil Dilaporkan:</td>
                                    <td class="text-end">Rp {{ number_format($laporan->total_biaya_rill_dilaporkan, 0, ',', '.') }}</td>
                                    <td colspan="3"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-sm text-muted">Tidak ada rincian biaya riil yang dilaporkan.</p>
                    @endif

                    {{-- Menampilkan referensi estimasi biaya jika ada --}}
                    @if($estimasiBiaya->isNotEmpty())
                    <h6 class="text-dark font-weight-bolder text-sm mt-4">Referensi Estimasi Biaya Awal</h6>
                    <hr class="horizontal dark mt-1 mb-2">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                             <thead class="thead-light">
                                <tr>
                                    <th class="text-xxs">Deskripsi Biaya (Estimasi)</th>
                                    <th class="text-xxs">Personil Terkait</th>
                                    <th class="text-xxs text-end">Subtotal (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estimasiBiaya as $est)
                                <tr>
                                    <td class="text-xs">{{ $est->deskripsi_biaya }}</td>
                                    <td class="text-xs">{{ $est->userTerkait->nama ?? 'Semua' }}</td>
                                    <td class="text-xs text-end">{{ number_format($est->subtotal_biaya,0,',','.') }}</td>
                                </tr>
                                @endforeach
                                <tr class="fw-bold">
                                    <td colspan="2" class="text-end">Total Estimasi Awal:</td>
                                    <td class="text-end">Rp {{ number_format($laporan->perjalananDinas->total_estimasi_biaya,0,',','.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @endif


                    <div class="text-center mt-4">
                        <a href="{{ route('pegawai.laporan-perjadin.index') }}" class="btn btn-secondary">Kembali ke Daftar Laporan</a>
                        @if(in_array($laporan->status_laporan, ['draft', 'revisi_laporan']) && ($laporan->user_id == Auth::id() || Auth::user()->hasAnyRole(['superadmin','operator'])))
                            <a href="{{ route('pegawai.laporan-perjadin.createOrEdit', $laporan->perjalanan_dinas_id) }}" class="btn btn-info">Edit Laporan Ini</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection