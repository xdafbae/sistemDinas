{{-- resources/views/perjalanan_dinas/pegawai/_rincian_biaya_item.blade.php --}}
@php
    // Tentukan apakah ini item yang sudah ada (existing) atau item baru dari old input
    $isExisting = $existing ?? false;
    $prefix = $isExisting ? "existing_rincian_biaya[{$index}]" : "rincian_biaya[{$index}]";
    $itemId = $isExisting ? ($rincian->id ?? $index) : $index; // Gunakan ID rincian jika ada, jika tidak, gunakan indeks
@endphp

<div class="rincian-item card card-body border mb-3">
    @if($isExisting && isset($rincian->id))
        <input type="hidden" name="{{ $prefix }}[id]" value="{{ $rincian->id }}">
    @endif
    <h6 class="text-xs font-weight-bolder">{{ $isExisting ? 'Edit Item Biaya #'.$itemId : 'Item Biaya Baru (dari validasi gagal)' }}</h6>
    <div class="row">
        <div class="col-md-5 mb-2">
            <label for="{{ $prefix }}_deskripsi" class="form-label">Deskripsi Biaya <span class="text-danger">*</span></label>
            <input type="text" name="{{ $prefix }}[deskripsi]" id="{{ $prefix }}_deskripsi" value="{{ $rincian->deskripsi ?? ($rincian->deskripsi_biaya_rill ?? '') }}" class="form-control form-control-sm @error($prefix.'.deskripsi') is-invalid @enderror" required>
            @error($prefix.'.deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-2 mb-2">
            <label for="{{ $prefix }}_jumlah" class="form-label">Jumlah <span class="text-danger">*</span></label>
            <input type="number" name="{{ $prefix }}[jumlah]" id="{{ $prefix }}_jumlah" value="{{ $rincian->jumlah ?? ($rincian->jumlah_rill ?? 1) }}" class="form-control form-control-sm @error($prefix.'.jumlah') is-invalid @enderror" required min="1">
            @error($prefix.'.jumlah') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-2 mb-2">
            <label for="{{ $prefix }}_satuan" class="form-label">Satuan <span class="text-danger">*</span></label>
            <input type="text" name="{{ $prefix }}[satuan]" id="{{ $prefix }}_satuan" value="{{ $rincian->satuan ?? ($rincian->satuan_rill ?? '') }}" class="form-control form-control-sm @error($prefix.'.satuan') is-invalid @enderror" required placeholder="OH, Tiket, dll">
            @error($prefix.'.satuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3 mb-2">
            <label for="{{ $prefix }}_harga_satuan" class="form-label">Harga Satuan (Rp) <span class="text-danger">*</span></label>
            <input type="number" step="any" name="{{ $prefix }}[harga_satuan]" id="{{ $prefix }}_harga_satuan" value="{{ $rincian->harga_satuan ?? ($rincian->harga_satuan_rill ?? '') }}" class="form-control form-control-sm @error($prefix.'.harga_satuan') is-invalid @enderror" required min="0">
            @error($prefix.'.harga_satuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="row">
        <div class="col-md-5 mb-2">
            <label for="{{ $prefix }}_nomor_bukti" class="form-label">Nomor Bukti</label>
            <input type="text" name="{{ $prefix }}[nomor_bukti]" id="{{ $prefix }}_nomor_bukti" value="{{ $rincian->nomor_bukti ?? '' }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-7 mb-2">
            <label for="{{ $prefix }}_bukti_file" class="form-label">Upload Bukti @if($isExisting && !empty($rincian->path_bukti_file)) (Ganti) @else (Opsional) @endif</label>
            <input type="file" name="{{ $prefix }}[bukti_file]" id="{{ $prefix }}_bukti_file" class="form-control form-control-sm @error($prefix.'.bukti_file') is-invalid @enderror">
            @if($isExisting && !empty($rincian->path_bukti_file))
                <small class="form-text text-muted">File saat ini:
                    <a href="{{ Storage::url($rincian->path_bukti_file) }}" target="_blank">{{ Str::afterLast($rincian->path_bukti_file, '/') }}</a>
                    <input type="checkbox" name="{{ $prefix }}[remove_bukti_file]" value="1" id="remove_bukti_{{$itemId}}"> <label for="remove_bukti_{{$itemId}}" class="text-danger">Hapus Bukti</label>
                </small>
            @endif
            @error($prefix.'.bukti_file') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 mb-2">
            <label for="{{ $prefix }}_keterangan" class="form-label">Keterangan Tambahan</label>
            <textarea name="{{ $prefix }}[keterangan]" id="{{ $prefix }}_keterangan" class="form-control form-control-sm" rows="1">{{ $rincian->keterangan ?? ($rincian->keterangan_rill ?? '') }}</textarea>
        </div>
    </div>
    <button type="button" class="btn btn-danger btn-sm remove-rincian-btn mt-2 align-self-start" style="width: auto;">Hapus Item</button>
</div>