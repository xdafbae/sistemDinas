@extends('layouts.app')
@section('title', 'Detail User - ' . config('app.name'))
@section('page_name', 'Detail User: ' . ($user->nama ?? 'User'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 col-md-10 mx-auto">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Detail User: {{ $user->nama ?? '' }}</h6>
                    </div>
                </div>
                <div class="card-body px-4 pb-3">
                    <div class="row mb-2">
                        <div class="col-md-3 fw-bold">Nama:</div>
                        <div class="col-md-9">{{ $user->nama }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-3 fw-bold">Email:</div>
                        <div class="col-md-9">{{ $user->email }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-3 fw-bold">NIP:</div>
                        <div class="col-md-9">{{ $user->nip ?? '-' }}</div>
                    </div>
                    <div class="row mb-2"> {{-- BARIS BARU UNTUK NOMOR HP --}}
                        <div class="col-md-3 fw-bold">Nomor HP:</div>
                        <div class="col-md-9">{{ $user->nomor_hp ?? '-' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-3 fw-bold">Jabatan:</div>
                        <div class="col-md-9">{{ $user->jabatan ?? '-' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-3 fw-bold">Golongan:</div>
                        <div class="col-md-9">{{ $user->gol ?? '-' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-3 fw-bold">Roles:</div>
                        <div class="col-md-9">
                            @forelse ($user->roles as $role)
                                <span class="badge bg-gradient-info me-1">{{ $role->name }}</span>
                            @empty
                                <span class="text-muted">Belum ada role</span>
                            @endforelse
                        </div>
                    </div>
                     <div class="row mb-2">
                        <div class="col-md-3 fw-bold">Tanggal Dibuat:</div>
                        <div class="col-md-9">{{ $user->created_at ? $user->created_at->format('d M Y, H:i:s') : '-' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-3 fw-bold">Terakhir Update:</div>
                        <div class="col-md-9">{{ $user->updated_at ? $user->updated_at->format('d M Y, H:i:s') : '-' }}</div>
                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-info">Edit User</a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection