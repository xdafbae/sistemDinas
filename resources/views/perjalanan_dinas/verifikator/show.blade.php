@extends('layouts.app')
@section('title', 'Detail Verifikasi Perjalanan Dinas - ' . config('app.name'))
@section('page_name', 'Verifikasi: ' . $perjalananDinas->nomor_spt)

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
                    {{-- Tampilkan semua detail perjalanan dinas di sini --}}
                    {{-- Mirip dengan view show milik operator, tapi dengan form aksi verifikasi --}}
                    <h5>Informasi SPT</h5>
                    <p><strong>Nomor SPT:</strong> {{ $perjalananDinas->nomor_spt }}</p>
                    <p><strong>Tanggal SPT:</strong> {{ $perjalananDinas->tanggal_spt->translatedFormat('d F Y') }}</p>
                    <p><strong>Jenis Perjalanan:</strong> {{ ucfirst(str_replace('_', ' ', $perjalananDinas->jenis_spt)) }}</p>
                    <p><strong>Jenis Kegiatan:</strong> {{ $perjalananDinas->jenis_kegiatan }}</p>
                    <p><strong>Tujuan:</strong> {{ $perjalananDinas->tujuan_spt }}
                        @if($perjalananDinas->provinsi_tujuan_id)
                            (Provinsi: {{ $perjalananDinas->provinsi_tujuan_id }}
                            @if($perjalananDinas->kota_tujuan_id), Kota/Kab: {{ $perjalananDinas->kota_tujuan_id }}@endif)
                        @endif
                    </p>
                    <p><strong>Dasar SPT:</strong> {!! nl2br(e($perjalananDinas->dasar_spt)) !!}</p>
                    <p><strong>Uraian Maksud:</strong> {!! nl2br(e($perjalananDinas->uraian_spt)) !!}</p>
                    <p><strong>Diajukan Oleh:</strong> {{ $perjalananDinas->operator->nama ?? '-' }}</p>

                    <h5 class="mt-4">Pelaksana & Waktu</h5>
                    <p><strong>Personil:</strong>
                        @foreach($perjalananDinas->personil as $p)
                            <span class="badge bg-gradient-secondary">{{ $p->nama }}</span>
                        @endforeach
                    </p>
                    <p><strong>Tanggal Pelaksanaan:</strong> {{ $perjalananDinas->tanggal_mulai->translatedFormat('d F Y') }} s/d {{ $perjalananDinas->tanggal_selesai->translatedFormat('d F Y') }} ({{ $perjalananDinas->lama_hari }} hari)</p>

                    <h5 class="mt-4">Estimasi Biaya</h5>
                    <p><strong>Total Estimasi:</strong> Rp {{ number_format($perjalananDinas->total_estimasi_biaya, 0, ',', '.') }}</p>
                    @if($perjalananDinas->biayaDetails->isNotEmpty())
                    <table class="table table-sm">
                        <thead><tr><th>Deskripsi Biaya</th><th class="text-end">Subtotal</th></tr></thead>
                        <tbody>
                        @foreach($perjalananDinas->biayaDetails as $detail)
                        <tr>
                            <td>
                                {{ $detail->deskripsi_biaya }}
                                @if($detail->userTerkait) (untuk {{ $detail->userTerkait->nama }}) @endif
                                <small class="d-block text-muted">
                                    @if($detail->jumlah_personil_terkait > 1) {{ $detail->jumlah_personil_terkait }} org x @endif
                                    @if($detail->jumlah_hari_terkait > 0) {{ $detail->jumlah_hari_terkait }} hari/malam x @endif
                                    @if($detail->jumlah_unit > 0 && $detail->jumlah_unit != $detail->jumlah_hari_terkait) {{ $detail->jumlah_unit }} unit/kali x @endif
                                    Rp {{ number_format($detail->harga_satuan,0,',','.') }}
                                </small>
                            </td>
                            <td class="text-end">Rp {{ number_format($detail->subtotal_biaya,0,',','.') }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @else
                    <p class="text-muted"><em>Detail estimasi biaya tidak tersedia atau belum dihitung.</em></p>
                    @endif

                    <hr class="horizontal dark my-4">
                    <h5 class="mt-3">Form Verifikasi</h5>
                    <form action="{{ route('verifikator.perjalanan-dinas.process', $perjalananDinas->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="catatan_verifikator" class="form-label">Catatan Verifikator (Wajib diisi jika Revisi atau Tolak)</label>
                            <textarea class="form-control @error('catatan_verifikator') is-invalid @enderror" id="catatan_verifikator" name="catatan_verifikator" rows="3">{{ old('catatan_verifikator') }}</textarea>
                            @error('catatan_verifikator') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" name="aksi_verifikasi" value="revisi" class="btn btn-warning me-2">Revisi ke Operator</button>
                            <button type="submit" name="aksi_verifikasi" value="tolak" class="btn btn-danger me-2">Tolak Pengajuan</button>
                            <button type="submit" name="aksi_verifikasi" value="setuju" class="btn btn-success">Setujui & Teruskan ke Atasan</button>
                        </div>
                    </form>
                     <div class="mt-3 text-start">
                        <a href="{{ route('verifikator.perjalanan-dinas.index') }}" class="btn btn-outline-secondary btn-sm">Kembali ke Daftar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection