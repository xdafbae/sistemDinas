@extends('layouts.app')
@section('title', 'Detail Persetujuan Perjalanan Dinas - ' . config('app.name'))
@section('page_name', 'Persetujuan: ' . ($perjalananDinas->nomor_spt ?? 'Pengajuan Baru'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-10 col-md-12 mx-auto">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-success shadow-success border-radius-lg pt-4 pb-3"> {{-- Warna hijau untuk persetujuan --}}
                        <h6 class="text-white text-capitalize ps-3">Detail Pengajuan untuk Persetujuan</h6>
                    </div>
                </div>
                <div class="card-body px-4 pb-3">
                    @if ($errors->any())
                        <div class="alert alert-danger text-white" role="alert">
                            <strong class="d-block">Perhatian! Terdapat kesalahan input:</strong>
                            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif

                    {{-- Bagian Detail Perjalanan Dinas --}}
                    <h5 class="text-dark font-weight-bold">Informasi SPT Awal</h5>
                    <p><strong>Nomor SPT Sementara:</strong>
                        @if($perjalananDinas->nomor_spt)
                            {{ $perjalananDinas->nomor_spt }}
                        @else
                            <span class="fst-italic text-muted">Belum Ditetapkan</span>
                        @endif
                    </p>
                    <p><strong>Tanggal SPT Diajukan:</strong> {{ $perjalananDinas->tanggal_spt->translatedFormat('d F Y') }}</p>
                    <p><strong>Jenis Perjalanan:</strong> {{ ucfirst(str_replace('_', ' ', $perjalananDinas->jenis_spt)) }}</p>
                    <p><strong>Jenis Kegiatan:</strong> {{ $perjalananDinas->jenis_kegiatan }}</p>
                    <p><strong>Tujuan:</strong> {{ $perjalananDinas->tujuan_spt }}
                        @if($perjalananDinas->provinsi_tujuan_id)
                            (Provinsi: {{ $perjalananDinas->provinsiTujuan->nama_provinsi ?? $perjalananDinas->provinsi_tujuan_id }}
                            @if($perjalananDinas->kota_tujuan_id), Kota/Kab: {{ $perjalananDinas->kotaTujuan->nama_kota ?? $perjalananDinas->kota_tujuan_id }}@endif)
                        @endif
                    </p>
                    <div> <strong>Dasar SPT:</strong>
                        <div class="p-1 border rounded bg-light mt-1" style="white-space: pre-wrap;">{!! nl2br(e($perjalananDinas->dasar_spt)) !!}</div>
                    </div>
                    <div class="mt-2"> <strong>Uraian Maksud:</strong>
                        <div class="p-1 border rounded bg-light mt-1" style="white-space: pre-wrap;">{!! nl2br(e($perjalananDinas->uraian_spt)) !!}</div>
                    </div>
                    <p class="mt-2"><strong>Diajukan Oleh (Operator):</strong> {{ $perjalananDinas->operator->nama ?? '-' }}</p>
                    <p><strong>Diverifikasi Oleh:</strong> {{ $perjalananDinas->verifikator->nama ?? '-' }}</p>
                    @if($perjalananDinas->catatan_verifikator)
                    <div class="alert alert-light p-2 mt-2" role="alert">
                        <strong>Catatan Verifikator:</strong><br>
                        {!! nl2br(e($perjalananDinas->catatan_verifikator)) !!}
                    </div>
                    @endif

                    <h5 class="mt-4 text-dark font-weight-bold">Pelaksana & Waktu</h5>
                    <p><strong>Personil:</strong>
                        @forelse($perjalananDinas->personil as $p)
                            <span class="badge bg-gradient-secondary me-1">{{ $p->nama }}</span>
                        @empty
                            <span class="fst-italic text-muted">Tidak ada data personil.</span>
                        @endforelse
                    </p>
                    <p><strong>Tanggal Pelaksanaan:</strong> {{ $perjalananDinas->tanggal_mulai->translatedFormat('d F Y') }} s/d {{ $perjalananDinas->tanggal_selesai->translatedFormat('d F Y') }} ({{ $perjalananDinas->lama_hari }} hari)</p>

                    <h5 class="mt-4 text-dark font-weight-bold">Estimasi Biaya</h5>
                    <p><strong>Total Estimasi:</strong> Rp {{ number_format($perjalananDinas->total_estimasi_biaya, 0, ',', '.') }}</p>
                    {{-- Tabel Detail Biaya (jika ingin ditampilkan ke Atasan) --}}
                    @if($perjalananDinas->biayaDetails->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>Deskripsi Biaya</th><th class="text-end">Subtotal</th></tr></thead>
                            <tbody>
                            @foreach($perjalananDinas->biayaDetails as $detail)
                            <tr>
                                <td>
                                    {{ $detail->deskripsi_biaya }}
                                    @if($detail->userTerkait) <small>(untuk {{ $detail->userTerkait->nama }})</small> @endif
                                </td>
                                <td class="text-end">Rp {{ number_format($detail->subtotal_biaya,0,',','.') }}</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    <hr class="horizontal dark my-4">
                    <h5 class="mt-3 text-dark font-weight-bold">Form Persetujuan Atasan</h5>
                    <form action="{{ route('atasan.perjalanan-dinas.process', $perjalananDinas->id) }}" method="POST" id="form-persetujuan">
                        @csrf
                        <div class="mb-3" id="tanggal-spt-final-group">
                            <label for="tanggal_spt_final" class="form-label">Tanggal Penetapan SPT <span class="text-danger" id="tanggal_spt_final_star" style="display: none;">*</span></label>
                            <input type="date" class="form-control @error('tanggal_spt_final') is-invalid @enderror"
                                   id="tanggal_spt_final" name="tanggal_spt_final"
                                   value="{{ old('tanggal_spt_final', $perjalananDinas->tanggal_spt ? $perjalananDinas->tanggal_spt->format('Y-m-d') : date('Y-m-d')) }}">
                            @error('tanggal_spt_final') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="form-text text-muted">Tanggal ini akan menjadi tanggal resmi SPT jika disetujui. Nomor SPT akan digenerate berdasarkan tanggal ini.</small>
                        </div>

                        <div class="mb-3">
                            <label for="catatan_atasan" class="form-label">Catatan Atasan (Wajib diisi jika Revisi atau Tolak)</label>
                            <textarea class="form-control @error('catatan_atasan') is-invalid @enderror" id="catatan_atasan" name="catatan_atasan" rows="3">{{ old('catatan_atasan') }}</textarea>
                            @error('catatan_atasan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" name="aksi_persetujuan" value="revisi" class="btn btn-warning me-2">Revisi ke Operator</button>
                            <button type="submit" name="aksi_persetujuan" value="tolak" class="btn btn-danger me-2">Tolak Pengajuan</button>
                            <button type="submit" name="aksi_persetujuan" value="setuju" class="btn btn-success">Setujui & Terbitkan SPT</button>
                        </div>
                    </form>
                     <div class="mt-4 text-start"> {{-- Tombol kembali di bawah form --}}
                        <a href="{{ route('atasan.perjalanan-dinas.index') }}" class="btn btn-outline-secondary btn-sm">Kembali ke Daftar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Logika JS jika ingin mengubah tampilan field tanggal SPT Final berdasarkan pilihan aksi
        const form = document.getElementById('form-persetujuan');
        const tanggalSptFinalGroup = document.getElementById('tanggal-spt-final-group');
        const tanggalSptFinalStar = document.getElementById('tanggal_spt_final_star');
        const tanggalSptFinalInput = document.getElementById('tanggal_spt_final');

        // Fungsi untuk update tampilan field tanggal
        // Untuk kesederhanaan, validasi utama tetap di backend.
        // Script ini lebih untuk UX.

        // Event listener pada tombol submit untuk set custom validity jika diperlukan
        form.addEventListener('submit', function(event) {
            const clickedButton = event.submitter; // Tombol mana yang ditekan
            if (clickedButton && clickedButton.name === 'aksi_persetujuan' && clickedButton.value === 'setuju') {
                if (!tanggalSptFinalInput.value) {
                    tanggalSptFinalInput.setCustomValidity('Tanggal Penetapan SPT wajib diisi jika menyetujui.');
                    tanggalSptFinalInput.reportValidity(); // Tampilkan pesan error browser default
                    event.preventDefault(); // Hentikan submit jika tanggal kosong saat setuju
                } else {
                    tanggalSptFinalInput.setCustomValidity(''); // Hapus custom validity jika valid
                }
            } else {
                 tanggalSptFinalInput.setCustomValidity(''); // Hapus custom validity untuk aksi lain
            }

            // Validasi catatan atasan jika revisi atau tolak
            const catatanAtasanInput = document.getElementById('catatan_atasan');
            if (clickedButton && clickedButton.name === 'aksi_persetujuan' && (clickedButton.value === 'revisi' || clickedButton.value === 'tolak')) {
                if (!catatanAtasanInput.value.trim()) {
                    catatanAtasanInput.setCustomValidity('Catatan Atasan wajib diisi untuk aksi Revisi atau Tolak.');
                    catatanAtasanInput.reportValidity();
                    event.preventDefault();
                } else {
                    catatanAtasanInput.setCustomValidity('');
                }
            } else {
                catatanAtasanInput.setCustomValidity('');
            }
        });
    });
</script>
@endpush
