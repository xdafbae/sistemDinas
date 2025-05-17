<div class="row">
    <div class="col-md-6 mb-3">
        <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
        <input class="form-control @error('nama') is-invalid @enderror" type="text" name="nama" id="nama" value="{{ old('nama', $user->nama ?? '') }}" required>
        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
        <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}" required>
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="nip" class="form-label">NIP</label>
        <input class="form-control @error('nip') is-invalid @enderror" type="text" name="nip" id="nip" value="{{ old('nip', $user->nip ?? '') }}">
        @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="jabatan" class="form-label">Jabatan</label>
        <input class="form-control @error('jabatan') is-invalid @enderror" type="text" name="jabatan" id="jabatan" value="{{ old('jabatan', $user->jabatan ?? '') }}">
        @error('jabatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="gol" class="form-label">Golongan</label>
        <input class="form-control @error('gol') is-invalid @enderror" type="text" name="gol" id="gol" value="{{ old('gol', $user->gol ?? '') }}">
        @error('gol') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="roles" class="form-label">Roles</label>
        <select name="roles[]" id="roles" class="form-control @error('roles') is-invalid @enderror @error('roles.*') is-invalid @enderror" multiple>
            {{-- Pastikan $roles di-pass dari controller (untuk create & edit) --}}
            {{-- Pastikan $userRoles di-pass dari controller (untuk edit) atau di-set array kosong (untuk create) --}}
            @foreach ($roles ?? [] as $id => $name)
                <option value="{{ $id }}" {{ (in_array($id, old('roles', $userRoles ?? []))) ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach
        </select>
        @error('roles') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @error('roles.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>
</div>

<hr class="horizontal dark my-3">
<p class="text-uppercase text-sm">Password</p>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="password" class="form-label">Password {{ isset($user) && $user->exists ? '(Kosongkan jika tidak ingin diubah)' : '' }} <span class="text-danger">{{ isset($user) && $user->exists ? '' : '*' }}</span></label>
        <input class="form-control @error('password') is-invalid @enderror" type="password" name="password" id="password" {{ isset($user) && $user->exists ? '' : 'required' }}>
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">{{ isset($user) && $user->exists ? '' : '*' }}</span></label>
        <input class="form-control" type="password" name="password_confirmation" id="password_confirmation" {{ isset($user) && $user->exists ? '' : 'required' }}>
    </div>
</div>

<div class="text-end mt-4">
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a>
    <button type="submit" class="btn btn-primary">{{ isset($user) && $user->exists ? 'Update User' : 'Simpan User' }}</button>
</div>