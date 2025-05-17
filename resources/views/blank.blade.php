@extends('layouts.app')

@section('title', 'Blank Page - Argon Dashboard')

@section('page_name', 'Blank Page')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header pb-0">
        <h6>Blank Page</h6>
        <p class="text-sm">Gunakan halaman ini sebagai template dasar untuk fitur baru</p>
      </div>
      <div class="card-body px-0 pt-0 pb-2">
        <div class="p-4">
          <!-- Konten halaman dimulai di sini -->
          <p>Ini adalah halaman blank yang bisa digunakan sebagai patokan dasar untuk membuat fitur baru.</p>
          
          <!-- Contoh card -->
          <div class="row mt-4">
            <div class="col-lg-6">
              <div class="card">
                <div class="card-header pb-0 p-3">
                  <h6 class="mb-0">Card Title</h6>
                </div>
                <div class="card-body p-3">
                  <p>Konten card di sini. Anda bisa menambahkan tabel, form, atau elemen lainnya.</p>
                </div>
              </div>
            </div>
            
            <div class="col-lg-6">
              <div class="card">
                <div class="card-header pb-0 p-3">
                  <h6 class="mb-0">Card Title</h6>
                </div>
                <div class="card-body p-3">
                  <p>Konten card di sini. Anda bisa menambahkan tabel, form, atau elemen lainnya.</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Contoh tabel sederhana -->
          <div class="row mt-4">
            <div class="col-12">
              <div class="card">
                <div class="card-header pb-0 p-3">
                  <h6 class="mb-0">Contoh Tabel</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                  <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                      <thead>
                        <tr>
                          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama</th>
                          <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Posisi</th>
                          <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                          <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal</th>
                          <th class="text-secondary opacity-7"></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>
                            <div class="d-flex px-2 py-1">
                              <div>
                                <img src="{{ asset('assets/img/team-2.jpg') }}" class="avatar avatar-sm me-3" alt="user1">
                              </div>
                              <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm">John Doe</h6>
                                <p class="text-xs text-secondary mb-0">john@example.com</p>
                              </div>
                            </div>
                          </td>
                          <td>
                            <p class="text-xs font-weight-bold mb-0">Manager</p>
                            <p class="text-xs text-secondary mb-0">Organization</p>
                          </td>
                          <td class="align-middle text-center text-sm">
                            <span class="badge badge-sm bg-gradient-success">Online</span>
                          </td>
                          <td class="align-middle text-center">
                            <span class="text-secondary text-xs font-weight-bold">23/04/18</span>
                          </td>
                          <td class="align-middle">
                            <a href="javascript:;" class="text-secondary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Edit user">
                              Edit
                            </a>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Contoh form sederhana -->
          <div class="row mt-4">
            <div class="col-12">
              <div class="card">
                <div class="card-header pb-0 p-3">
                  <h6 class="mb-0">Contoh Form</h6>
                </div>
                <div class="card-body p-3">
                  <form>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="example-text-input" class="form-control-label">Nama</label>
                          <input class="form-control" type="text" id="example-text-input">
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="example-email-input" class="form-control-label">Email</label>
                          <input class="form-control" type="email" id="example-email-input">
                        </div>
                      </div>
                      <div class="col-md-12">
                        <div class="form-group">
                          <label for="example-textarea" class="form-control-label">Deskripsi</label>
                          <textarea class="form-control" id="example-textarea" rows="3"></textarea>
                        </div>
                      </div>
                      <div class="col-md-12">
                        <button type="submit" class="btn bg-gradient-primary">Submit</button>
                        <button type="button" class="btn bg-gradient-secondary">Cancel</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <!-- Konten halaman berakhir di sini -->
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<!-- Tambahkan CSS khusus untuk halaman ini di sini -->
<style>
  /* Contoh CSS khusus halaman */
  .custom-class {
    background-color: #f7fafc;
    border-radius: 0.5rem;
    padding: 1rem;
  }
</style>
@endpush

@push('scripts')
<!-- Tambahkan JavaScript khusus untuk halaman ini di sini -->
<script>
  // Contoh JavaScript khusus halaman
  document.addEventListener('DOMContentLoaded', function() {
    console.log('Halaman blank telah dimuat');
    
    // Tambahkan kode JavaScript Anda di sini
  });
</script>
@endpush