<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4" id="sidenav-main">
    <div class="sidenav-header">
        {{-- Tombol tutup sidebar untuk tampilan kecil --}}
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ route('home') }}">
            <img src="{{ asset('assets/img/logo-ct-dark.png') }}" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold">{{ config('app.name', 'Sistem Dinas') }}</span>
        </a>
    </div>

    <hr class="horizontal dark mt-0">

    <div class="collapse navbar-collapse w-auto h-auto max-height-vh-100" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            {{-- Menu Dashboard --}}
            <li class="nav-item">
                <a class="nav-link {{ Route::is('home') ? 'active' : '' }}" href="{{ route('home') }}">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>

            {{-- Seksi Perjalanan Dinas --}}
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Perjalanan Dinas</h6>
            </li>

            {{-- Pengajuan Baru --}}
            @if(Auth::user()->hasAnyRole(['operator', 'superadmin']))
            <li class="nav-item">
                <a class="nav-link {{ Route::is('operator.perjalanan-dinas.create') ? 'active' : '' }}" href="{{ route('operator.perjalanan-dinas.create') }}">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-fat-add text-info text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Buat Pengajuan Baru</span>
                </a>
            </li>
            @endif

            {{-- Daftar Pengajuan Saya (Operator) --}}
            @hasrole('operator')
            <li class="nav-item">
                <a class="nav-link {{ Route::is('operator.perjalanan-dinas.index') ? 'active' : '' }}" href="{{ route('operator.perjalanan-dinas.index') }}">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-bullet-list-67 text-primary text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Pengajuan Saya</span>
                </a>
            </li>
            @endhasrole

            {{-- Verifikasi Pengajuan --}}
            @if(Auth::user()->hasAnyRole(['verifikator', 'superadmin']))
            <li class="nav-item">
                <a class="nav-link {{ Route::is('verifikator.perjalanan-dinas.index') ? 'active' : '' }}" href="{{ route('verifikator.perjalanan-dinas.index') }}">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-check-bold text-warning text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Verifikasi Pengajuan</span>
                </a>
            </li>
            @endif

            {{-- Persetujuan Atasan --}}
            @if(Auth::user()->hasAnyRole(['atasan', 'kepala dinas', 'superadmin']))
            <li class="nav-item">
                <a class="nav-link {{ Route::is('atasan.perjalanan-dinas.index') ? 'active' : '' }}" href="{{ route('atasan.perjalanan-dinas.index') }}">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-like-2 text-success text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Persetujuan Atasan</span>
                </a>
            </li>
            @endif

            {{-- Dokumen SPT/SPPD --}}
            @auth
            <li class="nav-item">
                <a class="nav-link {{ Route::is('dokumen.index') ? 'active' : '' }}" href="{{ route('dokumen.index') }}">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-folder-17 text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Dokumen SPT/SPPD</span>
                </a>
            </li>
            @endauth

            {{-- Laporan Perjalanan Dinas --}}
            @if(Auth::user()->hasAnyRole(['pegawai', 'superadmin']))
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-single-copy-04 text-info text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Laporan Perjalanan Saya</span>
                </a>
            </li>
            @endif

            {{-- Monitoring Semua Perjalanan (Superadmin saja) --}}
            @hasrole('superadmin')
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-archive-2 text-danger text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Monitoring Semua Perjadin</span>
                </a>
            </li>
            @endhasrole

            {{-- Administrasi Sistem --}}
            @can('manage users')
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Administrasi Sistem</h6>
            </li>
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#usersManajemenSubmenu" class="nav-link {{ Request::is('admin/users*') || Request::is('admin/roles*') ? 'active' : '' }}" aria-controls="usersManajemenSubmenu" role="button" aria-expanded="{{ Request::is('admin/users*') || Request::is('admin/roles*') ? 'true' : 'false' }}">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-circle-08 text-warning text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Manajemen Pengguna</span>
                </a>
                <div class="collapse {{ Request::is('admin/users*') || Request::is('admin/roles*') ? 'show' : '' }}" id="usersManajemenSubmenu">
                    <ul class="nav ms-4 ps-3">
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/users*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                <span class="sidenav-mini-icon"> U </span>
                                <span class="sidenav-normal"> Daftar Pengguna </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Manajemen SBU (Hanya Superadmin) --}}
            @hasrole('superadmin')
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-money-coins text-success text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Manajemen SBU</span>
                </a>
            </li>
            @endhasrole
            @endcan

            {{-- Akun Saya --}}
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Akun Saya</h6>
            </li>
            @auth
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Profil Saya</span>
                </a>
            </li>
            @endauth
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                    <div class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-button-power text-danger text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Keluar</span>
                </a>
                <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</aside>
