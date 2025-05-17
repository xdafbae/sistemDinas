<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 " id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ route('home') }}" target="_blank">
            <img src="{{ asset('assets/img/logo-ct-dark.png') }}" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold">{{ config('app.name', 'Kepegawaian') }}</span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse  w-auto h-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link {{ Route::is('home') ? 'active' : '' }}" href="{{ route('home') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>

            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Manajemen Data</h6>
            </li>

            {{-- Contoh Menu untuk Pegawai Biasa --}}
            @hasanyrole('pegawai|atasan|verifikator|operator|kepala dinas|superadmin')
            <li class="nav-item">
                <a class="nav-link {{-- Route::is('profil.show') ? 'active' : '' --}}" href="{{-- route('profil.show') --}}#"> {{-- Ganti dengan route profil Anda --}}
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Profil Saya</span>
                </a>
            </li>
            @endhasanyrole

            {{-- Contoh Menu yang mungkin hanya untuk Atasan/Kepala Dinas --}}
            @hasanyrole('atasan|kepala dinas|superadmin')
            <li class="nav-item">
                <a class="nav-link {{-- Route::is('persetujuan.*') ? 'active' : '' --}}" href="{{-- route('persetujuan.index') --}}#"> {{-- Ganti dengan route persetujuan --}}
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-check-bold text-info text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Persetujuan</span>
                </a>
            </li>
            @endhasanyrole


            {{-- Menu Manajemen User hanya untuk Superadmin atau Operator --}}
            @can('manage users') {{-- Ganti dengan permission yang sesuai --}}
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#usersManajemenSubmenu" class="nav-link {{ Request::is('admin/users*') || Request::is('admin/roles*') ? 'active' : '' }}" aria-controls="usersManajemenSubmenu" role="button" aria-expanded="{{ Request::is('admin/users*') || Request::is('admin/roles*') ? 'true' : 'false' }}">
                    <div class="icon icon-shape icon-sm text-center d-flex align-items-center justify-content-center">
                        <i class="ni ni-circle-08 text-warning text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Manajemen User</span>
                </a>
                <div class="collapse {{ Request::is('admin/users*') || Request::is('admin/roles*') ? 'show' : '' }}" id="usersManajemenSubmenu">
                    <ul class="nav ms-4 ps-3">
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/users*') ? 'active' : '' }}" href="{{route ('admin.users.index') }}"> {{-- Ganti dengan route user index --}}
                                <span class="sidenav-mini-icon"> U </span>
                                <span class="sidenav-normal"> Daftar User </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/roles*') ? 'active' : '' }}" href="{{-- route('admin.roles.index') --}}#"> {{-- Ganti dengan route roles index --}}
                                <span class="sidenav-mini-icon"> R </span>
                                <span class="sidenav-normal"> Roles & Permissions </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan

            {{-- Contoh menu lain yang mungkin relevan --}}
            @hasanyrole('pegawai|operator|superadmin')
            <li class="nav-item">
                <a class="nav-link {{-- Route::is('laporan.*') ? 'active' : '' --}}" href="{{-- route('laporan.index') --}}#"> {{-- Ganti dengan route laporan --}}
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-collection text-success text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Laporan</span>
                </a>
            </li>
            @endhasanyrole


            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Akun</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link " href="#" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-button-power text-danger text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Logout</span>
                </a>
                <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </div>

    {{-- Bagian bawah sidebar jika ada, misal untuk info tambahan atau iklan --}}
    {{-- <div class="sidenav-footer mx-3 ">
        <div class="card card-plain shadow-none" id="sidenavCard">
            <img class="w-50 mx-auto" src="{{ asset('assets/img/illustrations/icon-documentation.svg') }}" alt="sidebar_illustration">
            <div class="card-body text-center p-3 w-100 pt-0">
                <div class="docs-info">
                    <h6 class="mb-0">Butuh Bantuan?</h6>
                    <p class="text-xs font-weight-bold mb-0">Lihat dokumentasi kami</p>
                </div>
            </div>
        </div>
        <a href="https://www.creative-tim.com/learning-lab/bootstrap/license/argon-dashboard" target="_blank" class="btn btn-dark btn-sm w-100 mb-3">Dokumentasi</a>
    </div> --}}
</aside>