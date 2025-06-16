<?php

namespace App\Http\Controllers;

use App\Models\PerjalananDinas;
use App\Models\LaporanPerjalananDinas;
use App\Models\LaporanPerjalananDinasRincianBiaya;
use App\Models\User; // Digunakan untuk notifikasi atau info user
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator; // Digunakan untuk validasi
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Str;
// use App\Notifications\LaporanPerluVerifikasi; // Komentari jika belum dibuat


class LaporanPerjalananDinasPegawaiController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        // Anda bisa menambahkan middleware role spesifik di sini jika semua method hanya untuk role tertentu,
        // atau biarkan otorisasi per method jika lebih fleksibel.
    }

    /**
     * Menampilkan daftar perjalanan dinas yang perlu atau sudah dilaporkan oleh user.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();
            $query = PerjalananDinas::with(['laporanUtama' => function ($query) use ($user) {
                // Jika laporan dibuat per user, filter laporan milik user yang login
                // $query->where('user_id', $user->id);
            }, 'operator'])
                ->whereIn('status', ['disetujui', 'selesai'])
                ->whereHas('personil', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })
                ->select('perjalanan_dinas.*')
                ->latest('perjalanan_dinas.tanggal_spt');

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('nomor_spt_display', fn($pd) => $pd->nomor_spt ?? '<em class="text-muted">Belum Ditetapkan</em>')
                ->addColumn('tanggal_spt_formatted', fn($pd) => $pd->tanggal_spt ? Carbon::parse($pd->tanggal_spt)->translatedFormat('d M Y') : '-')
                ->addColumn('tujuan_spt_display', fn($pd) => Str::limit($pd->tujuan_spt, 30))
                ->addColumn('tanggal_pelaksanaan', fn($pd) => Carbon::parse($pd->tanggal_mulai)->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($pd->tanggal_selesai)->translatedFormat('d M Y'))
                ->addColumn('status_laporan', function ($pd) {
                    // Ambil laporan yang dibuat oleh user yang login untuk perjalanan dinas ini
                    $laporanUser = $pd->semuaLaporan()->where('user_id', Auth::id())->first();
                    if ($laporanUser) {
                        $statusText = ucfirst(str_replace('_', ' ', $laporanUser->status_laporan));
                        $badgeClass = 'bg-secondary';
                        if ($laporanUser->status_laporan == 'draft') $badgeClass = 'bg-light text-dark border';
                        if ($laporanUser->status_laporan == 'diserahkan_untuk_verifikasi') $badgeClass = 'bg-info';
                        if ($laporanUser->status_laporan == 'revisi_laporan') $badgeClass = 'bg-warning';
                        if ($laporanUser->status_laporan == 'disetujui_bendahara' || $laporanUser->status_laporan == 'selesai_diproses') $badgeClass = 'bg-success';
                        return "<span class='badge {$badgeClass}'>{$statusText}</span>";
                    }
                    return '<span class="badge bg-light text-dark border">Belum Dibuat</span>';
                })
                ->addColumn('action', function ($perjalanan) use ($user) {
                    $laporanUser = $perjalanan->semuaLaporan()->where('user_id', $user->id)->first();
                    $actionButtons = '';
                    $isPelaksana = $perjalanan->personil()->where('users.id', $user->id)->exists();

                    if ($isPelaksana) {
                        if (!$laporanUser || in_array($laporanUser->status_laporan, ['draft', 'revisi_laporan'])) {
                            $actionButtons .= '<a href="' . route('pegawai.laporan-perjadin.createOrEdit', $perjalanan->id) . '" class="btn btn-primary btn-sm me-1" data-bs-toggle="tooltip" title="Buat/Edit Laporan"><i class="fas fa-edit"></i></a>';
                        }
                        if ($laporanUser && $laporanUser->status_laporan == 'draft') {
                            $actionButtons .= ' <form action="' . route('pegawai.laporan-perjadin.submit', $laporanUser->id) . '" method="POST" class="d-inline">' . csrf_field() . method_field('PATCH') . '<button type="submit" class="btn btn-success btn-sm" onclick="return confirm(\'Apakah Anda yakin ingin menyerahkan laporan ini?\')" data-bs-toggle="tooltip" title="Serahkan Laporan"><i class="fas fa-paper-plane"></i></button></form>';
                        }
                    }
                    // Tombol lihat selalu ada jika laporan sudah pernah dibuat oleh user ini, atau jika user punya hak global
                    if ($laporanUser || ($isPelaksana && !$laporanUser)) { // Jika user pelaksana tapi belum buat laporan, link create/edit sudah ada
                        if ($laporanUser) { // Hanya tampilkan tombol lihat jika laporan sudah ada
                            $actionButtons .= ' <a href="' . route('pegawai.laporan-perjadin.show', $perjalanan->id) . '?user_laporan_id=' . $user->id . '" class="btn btn-info btn-sm ms-1" data-bs-toggle="tooltip" title="Lihat Laporan Saya"><i class="fas fa-eye"></i></a>';
                        }
                    } else if ($user->hasAnyRole(['superadmin', 'operator', 'verifikator', 'atasan', 'kepala dinas', 'bendahara']) && $perjalanan->semuaLaporan()->exists()) {
                        // Admin dll bisa lihat laporan pertama yang ada (atau perlu UI untuk pilih laporan personil)
                        $actionButtons .= ' <a href="' . route('pegawai.laporan-perjadin.show', $perjalanan->id) . '" class="btn btn-info btn-sm ms-1" data-bs-toggle="tooltip" title="Lihat Laporan (Admin View)"><i class="fas fa-eye"></i></a>';
                    }


                    return $actionButtons ?: '-';
                })
                ->rawColumns(['action', 'nomor_spt_display', 'status_laporan'])
                ->make(true);
        }
        return view('perjalanan_dinas.pegawai.laporan_index');
    }

    /**
     * Menampilkan form untuk membuat atau mengedit laporan perjalanan dinas.
     */
    public function createOrEdit(PerjalananDinas $perjalananDinas)
    {
        $user = Auth::user();
        $isPelaksana = $perjalananDinas->personil()->where('users.id', $user->id)->exists();

        if (!$isPelaksana && !$user->hasAnyRole(['superadmin', 'operator'])) {
            abort(403, 'Anda tidak berhak membuat/mengedit laporan untuk perjalanan dinas ini.');
        }
        if (!in_array($perjalananDinas->status, ['disetujui', 'selesai'])) {
            return redirect()->route('pegawai.laporan-perjadin.index')->with('error', 'Laporan hanya bisa dibuat untuk perjalanan yang sudah disetujui/selesai.');
        }

        // Laporan dibuat spesifik oleh user yang login untuk perjalanan dinas ini
        $laporan = LaporanPerjalananDinas::firstOrNew(
            ['perjalanan_dinas_id' => $perjalananDinas->id, 'user_id' => $user->id],
            ['tanggal_laporan' => Carbon::now()->format('Y-m-d'), 'status_laporan' => 'draft'] // Nilai default jika baru
        );
        // Pastikan ringkasan_hasil_kegiatan tidak null jika baru dibuat dan kolomnya NOT NULL
        if ($laporan->wasRecentlyCreated && is_null($laporan->ringkasan_hasil_kegiatan)) {
            $laporan->ringkasan_hasil_kegiatan = ''; // Atau nilai default lain yang sesuai
        }


        if ($laporan->exists && !in_array($laporan->status_laporan, ['draft', 'revisi_laporan'])) {
            return redirect()->route('pegawai.laporan-perjadin.show', ['perjalananDinas' => $perjalananDinas->id, 'user_laporan_id' => $user->id])
                ->with('info', 'Laporan ini sudah diserahkan dan tidak dapat diedit lagi oleh Anda.');
        }

        $perjalananDinas->load(['biayaDetails.sbuItem', 'personil']);
        $estimasiBiaya = $perjalananDinas->biayaDetails;
        $rincianBiayaRill = $laporan->exists ? $laporan->rincianBiaya()->orderBy('id')->get() : collect();

        return view('perjalanan_dinas.pegawai.laporan_form', compact('perjalananDinas', 'laporan', 'estimasiBiaya', 'rincianBiayaRill'));
    }

    /**
     * Menyimpan laporan perjalanan dinas (baru atau update).
     */
    public function storeOrUpdate(Request $request, PerjalananDinas $perjalananDinas)
    {
        $user = Auth::user();
        $isPelaksana = $perjalananDinas->personil()->where('users.id', $user->id)->exists();

        // Otorisasi dasar
        if (!$isPelaksana && !$user->hasAnyRole(['superadmin', 'operator'])) { // Operator mungkin bisa edit jika ada kebijakan
            abort(403, 'Anda tidak berhak menyimpan laporan untuk perjalanan dinas ini.');
        }
        if (!in_array($perjalananDinas->status, ['disetujui', 'selesai'])) {
            return redirect()->route('pegawai.laporan-perjadin.index')->with('error', 'Laporan hanya bisa disimpan untuk perjalanan yang sudah disetujui/selesai.');
        }

        Log::info('Mulai storeOrUpdate Laporan Perjadin untuk Perjadin ID: ' . $perjalananDinas->id . ' oleh User ID: ' . $user->id);
        Log::info('Data Request:', $request->except('bukti_file')); // Jangan log isi file

        // Dapatkan atau buat instance laporan
        $laporan = LaporanPerjalananDinas::firstOrCreate(
            ['perjalanan_dinas_id' => $perjalananDinas->id, 'user_id' => $user->id],
            [ // Nilai default HANYA jika record BARU dibuat
                'tanggal_laporan' => $request->input('tanggal_laporan', Carbon::now()->format('Y-m-d')),
                'status_laporan' => 'draft',
                'ringkasan_hasil_kegiatan' => $request->input('ringkasan_hasil_kegiatan', '')
                // 'total_biaya_rill_dilaporkan' => 0, // JANGAN set default 0 di sini jika akan dihitung nanti
            ]
        );

        // Cek status laporan yang sudah ada
        if ($laporan->exists && !in_array($laporan->status_laporan, ['draft', 'revisi_laporan'])) {
            return redirect()->route('pegawai.laporan-perjadin.show', ['perjalananDinas' => $perjalananDinas->id, 'user_laporan_id' => $user->id])
                ->with('info', 'Laporan ini sudah diserahkan dan tidak dapat diubah lagi.');
        }

        // Validasi
        $validator = Validator::make($request->all(), [
            'tanggal_laporan' => 'required|date|before_or_equal:today',
            'ringkasan_hasil_kegiatan' => 'required|string|min:10|max:5000',
            'kendala_dihadapi' => 'nullable|string|max:2000',
            'saran_tindak_lanjut' => 'nullable|string|max:2000',
            'existing_rincian_biaya' => 'nullable|array',
            'existing_rincian_biaya.*.id' => 'required_with:existing_rincian_biaya|integer|exists:laporan_perjalanan_dinas_rincian_biaya,id',
            'existing_rincian_biaya.*.deskripsi' => 'required_with:existing_rincian_biaya.*.id|string|max:255',
            'existing_rincian_biaya.*.jumlah' => 'required_with:existing_rincian_biaya.*.id|integer|min:1',
            'existing_rincian_biaya.*.satuan' => 'required_with:existing_rincian_biaya.*.id|string|max:50',
            'existing_rincian_biaya.*.harga_satuan' => 'required_with:existing_rincian_biaya.*.id|numeric|min:0',
            'existing_rincian_biaya.*.nomor_bukti' => 'nullable|string|max:100',
            'existing_rincian_biaya.*.bukti_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', // Max 2MB
            'existing_rincian_biaya.*.remove_bukti_file' => 'nullable|in:1', // Untuk checkbox hapus file
            'existing_rincian_biaya.*.keterangan' => 'nullable|string|max:500',
            'rincian_biaya' => 'nullable|array', // Rincian baru
            'rincian_biaya.*.deskripsi' => 'required_with:rincian_biaya.*.jumlah,rincian_biaya.*.satuan,rincian_biaya.*.harga_satuan|nullable|string|max:255',
            'rincian_biaya.*.jumlah' => 'required_with:rincian_biaya.*.deskripsi|nullable|integer|min:1',
            'rincian_biaya.*.satuan' => 'required_with:rincian_biaya.*.deskripsi|nullable|string|max:50',
            'rincian_biaya.*.harga_satuan' => 'required_with:rincian_biaya.*.deskripsi|nullable|numeric|min:0',
            'rincian_biaya.*.nomor_bukti' => 'nullable|string|max:100',
            'rincian_biaya.*.bukti_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'rincian_biaya.*.keterangan' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            Log::warning('Validasi gagal saat store/update laporan perjadin:', $validator->errors()->toArray());
            return redirect()->route('pegawai.laporan-perjadin.createOrEdit', $perjalananDinas->id)
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $laporan->fill($request->only([
                'tanggal_laporan',
                'ringkasan_hasil_kegiatan',
                'kendala_dihadapi',
                'saran_tindak_lanjut'
            ]));

            if ($laporan->status_laporan == 'revisi_laporan' && $request->submit_action != 'serahkan') {
                $laporan->status_laporan = 'draft'; // Kembali ke draft jika diedit dari revisi dan belum diserahkan lagi
            } elseif (!$laporan->exists) { // Jika ini adalah pembuatan baru (meskipun firstOrCreate sudah handle)
                $laporan->status_laporan = 'draft';
            }
            $laporan->save();
            Log::info("Laporan utama ID {$laporan->id} disimpan/diupdate. Status: {$laporan->status_laporan}");

            $totalBiayaRillDilaporkan = 0;
            $currentRincianIds = []; // Untuk melacak ID rincian yang masih ada/diupdate

            // Proses existing rincian (update atau hapus berdasarkan input)
            if ($request->has('existing_rincian_biaya')) {
                Log::info('Memproses existing_rincian_biaya:', $request->existing_rincian_biaya);
                foreach ($request->existing_rincian_biaya as $idRincian => $rincianData) {
                    $rincian = LaporanPerjalananDinasRincianBiaya::find($idRincian);
                    if ($rincian && $rincian->laporan_perjalanan_dinas_id == $laporan->id) { // Pastikan milik laporan ini
                        // Cek apakah item ini akan dihapus (jika Anda menambahkan input untuk menandai hapus)
                        if (isset($rincianData['delete_item']) && $rincianData['delete_item'] == '1') {
                            if ($rincian->path_bukti_file && Storage::disk('public')->exists($rincian->path_bukti_file)) {
                                Storage::disk('public')->delete($rincian->path_bukti_file);
                            }
                            $rincian->delete();
                            Log::info("Rincian existing ID {$idRincian} dihapus.");
                            continue; // Lanjut ke item berikutnya
                        }

                        $jumlah = isset($rincianData['jumlah']) ? intval($rincianData['jumlah']) : 0;
                        $hargaSatuan = isset($rincianData['harga_satuan']) ? floatval($rincianData['harga_satuan']) : 0;
                        $subtotal = $jumlah * $hargaSatuan;
                        Log::info("Updating Rincian ID {$idRincian}: Deskripsi={$rincianData['deskripsi']}, Jumlah={$jumlah}, HargaSatuan={$hargaSatuan}, Subtotal={$subtotal}");

                        $pathBukti = $rincian->path_bukti_file; // Pertahankan path lama defaultnya
                        if ($request->hasFile("existing_rincian_biaya.{$idRincian}.bukti_file")) {
                            // Hapus file lama jika ada dan ada file baru
                            if ($pathBukti && Storage::disk('public')->exists($pathBukti)) {
                                Storage::disk('public')->delete($pathBukti);
                                Log::info("Bukti lama {$pathBukti} untuk rincian ID {$idRincian} dihapus.");
                            }
                            $file = $request->file("existing_rincian_biaya.{$idRincian}.bukti_file");
                            $fileName = time() . '_' . Str::random(5) . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                            $pathBukti = $file->storeAs("bukti_perjadin/{$laporan->id}", $fileName, 'public');
                            Log::info("Bukti baru untuk rincian ID {$idRincian} disimpan di {$pathBukti}.");
                        } elseif ($request->input("existing_rincian_biaya.{$idRincian}.remove_bukti_file") == '1') {
                            if ($pathBukti && Storage::disk('public')->exists($pathBukti)) {
                                Storage::disk('public')->delete($pathBukti);
                                Log::info("Bukti {$pathBukti} untuk rincian ID {$idRincian} dihapus berdasarkan checkbox.");
                            }
                            $pathBukti = null;
                        }

                        $rincian->update([
                            'deskripsi_biaya_rill' => $rincianData['deskripsi'],
                            'jumlah_rill' => $jumlah,
                            'satuan_rill' => $rincianData['satuan'],
                            'harga_satuan_rill' => $hargaSatuan,
                            'subtotal_biaya_rill' => $subtotal,
                            'nomor_bukti' => $rincianData['nomor_bukti'] ?? null,
                            'path_bukti_file' => $pathBukti,
                            'keterangan_rill' => $rincianData['keterangan'] ?? null,
                        ]);
                        $totalBiayaRillDilaporkan += $subtotal;
                        $currentRincianIds[] = $rincian->id; // Tambahkan ID yang dipertahankan/diupdate
                    } else {
                        Log::warning("Rincian existing ID {$idRincian} tidak ditemukan atau bukan milik laporan ID {$laporan->id}.");
                    }
                }
            }
            // Hapus rincian dari database yang tidak ada lagi dalam array $currentRincianIds (artinya dihapus dari form oleh user)
            // Ini hanya berlaku jika Anda tidak mengirimkan semua item rincian dari form setiap saat (misalnya, jika item yang dihapus benar-benar dihilangkan dari DOM).
            // Jika semua item (termasuk yang 'dihapus' dengan flag) dikirim, maka logic di atas sudah cukup.
            // Untuk kasus di mana item yang dihapus dari DOM tidak dikirim sama sekali:
            if ($laporan->exists) { // Hanya jika laporan sudah ada sebelumnya
                $laporan->rincianBiaya()->whereNotIn('id', $currentRincianIds)->get()->each(function ($rincianToDelete) {
                    if ($rincianToDelete->path_bukti_file && Storage::disk('public')->exists($rincianToDelete->path_bukti_file)) {
                        Storage::disk('public')->delete($rincianToDelete->path_bukti_file);
                        Log::info("Bukti {$rincianToDelete->path_bukti_file} untuk rincian ID {$rincianToDelete->id} dihapus karena item rincian dihapus.");
                    }
                    $rincianToDelete->delete();
                });
            }


            // Proses rincian baru
            if ($request->has('rincian_biaya')) {
                Log::info('Memproses rincian_biaya baru:', $request->rincian_biaya);
                foreach ($request->rincian_biaya as $index => $rincianData) {
                    // Cek jika ini adalah template kosong yang tidak diisi (jika JS gagal menghapusnya)
                    if (empty($rincianData['deskripsi']) && empty($rincianData['jumlah']) && empty($rincianData['satuan']) && empty($rincianData['harga_satuan'])) {
                        Log::info("Skipping rincian baru (index: {$index}) karena field wajib kosong.");
                        continue;
                    }
                    $jumlah = isset($rincianData['jumlah']) ? intval($rincianData['jumlah']) : 0;
                    $hargaSatuan = isset($rincianData['harga_satuan']) ? floatval($rincianData['harga_satuan']) : 0;
                    $subtotal = $jumlah * $hargaSatuan;
                    Log::info("Creating Rincian Baru: Deskripsi={$rincianData['deskripsi']}, Jumlah={$jumlah}, HargaSatuan={$hargaSatuan}, Subtotal={$subtotal}");

                    $pathBukti = null;
                    if ($request->hasFile("rincian_biaya.{$index}.bukti_file")) {
                        $file = $request->file("rincian_biaya.{$index}.bukti_file");
                        $fileName = time() . '_' . Str::random(5) . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                        $pathBukti = $file->storeAs("bukti_perjadin/{$laporan->id}", $fileName, 'public');
                        Log::info("Bukti baru untuk rincian baru index {$index} disimpan di {$pathBukti}.");
                    }
                    $laporan->rincianBiaya()->create([
                        'deskripsi_biaya_rill' => $rincianData['deskripsi'],
                        'jumlah_rill' => $jumlah,
                        'satuan_rill' => $rincianData['satuan'],
                        'harga_satuan_rill' => $hargaSatuan,
                        'subtotal_biaya_rill' => $subtotal,
                        'nomor_bukti' => $rincianData['nomor_bukti'] ?? null,
                        'path_bukti_file' => $pathBukti,
                        'keterangan_rill' => $rincianData['keterangan'] ?? null,
                    ]);
                    $totalBiayaRillDilaporkan += $subtotal;
                }
            }
            $laporan->total_biaya_rill_dilaporkan = $totalBiayaRillDilaporkan;
            $laporan->save();

            Log::info("Laporan ID {$laporan->id} disimpan dengan total biaya riil: {$totalBiayaRillDilaporkan}");


            DB::commit();

            $pesanSukses = 'Laporan perjalanan dinas berhasil disimpan sebagai draft.';
            if ($request->submit_action == 'serahkan') {
                // Pastikan laporan yang disubmit adalah yang baru saja diupdate/dibuat
                // $laporanFresh = LaporanPerjalananDinas::find($laporan->id); // atau $laporan->fresh()
                return $this->submitLaporan($laporan); // <<< PERBAIKAN DI SINI: Hanya kirim $laporan
            }
            return redirect()->route('pegawai.laporan-perjadin.createOrEdit', $perjalananDinas->id)->with('success', $pesanSukses);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saat menyimpan laporan perjalanan dinas ID {$laporan->id}: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Gagal menyimpan laporan: Terjadi kesalahan internal. Silakan coba lagi.')->withInput();
        }
    }

    public function submitLaporan(LaporanPerjalananDinas $laporan) // Definisi method tetap, hanya menerima $laporan
    {
        $user = Auth::user();
        if ($laporan->user_id != $user->id || !in_array($laporan->status_laporan, ['draft', 'revisi_laporan'])) {
            abort(403, 'Anda tidak berhak menyerahkan laporan ini atau status laporan tidak valid.');
        }

        if (empty($laporan->ringkasan_hasil_kegiatan)) {
            return redirect()->route('pegawai.laporan-perjadin.createOrEdit', $laporan->perjalanan_dinas_id)
                ->with('error', 'Ringkasan Hasil Kegiatan wajib diisi sebelum menyerahkan laporan.');
        }
        // Tambahkan validasi lain jika perlu sebelum submit (misalnya, minimal ada 1 rincian jika total > 0)
        if ($laporan->total_biaya_rill_dilaporkan > 0 && $laporan->rincianBiaya()->count() == 0) {
            return redirect()->route('pegawai.laporan-perjadin.createOrEdit', $laporan->perjalanan_dinas_id)
                ->with('error', 'Laporan dengan total biaya riil lebih dari nol harus memiliki minimal satu rincian biaya.');
        }


        $laporan->status_laporan = 'diserahkan_untuk_verifikasi';
        $laporan->catatan_pereview = null; // Hapus catatan pereview lama saat diserahkan kembali
        $laporan->save();

        // Kirim notifikasi ke Verifikator Laporan/Bendahara
        // $bendaharaUsers = User::role('bendahara')->get(); // Ganti 'bendahara' dengan role yang sesuai
        // if ($bendaharaUsers->isNotEmpty()) {
        //     // Pastikan Anda sudah membuat notifikasi App\Notifications\LaporanPerluVerifikasi
        //     // Notification::send($bendaharaUsers, new LaporanPerluVerifikasi($laporan, $user));
        //     Log::info("Notifikasi Laporan Perlu Verifikasi akan dikirim ke bendahara untuk laporan ID: {$laporan->id}");
        // }

        return redirect()->route('pegawai.laporan-perjadin.index')->with('success', 'Laporan perjalanan dinas berhasil diserahkan untuk verifikasi.');
    }
    public function showLaporan(Request $request, PerjalananDinas $perjalananDinas)
    {
        $user = Auth::user();
        $laporan = null;

        // Jika ada parameter user_laporan_id (misal dari link admin/atasan)
        $userLaporanId = $request->query('user_laporan_id');
        if ($userLaporanId && $user->hasAnyRole(['superadmin', 'operator', 'verifikator', 'atasan', 'kepala dinas', 'bendahara'])) {
            $laporan = LaporanPerjalananDinas::where('perjalanan_dinas_id', $perjalananDinas->id)
                ->where('user_id', $userLaporanId)
                ->first();
        } else {
            // Ambil laporan milik user yang login untuk perjalanan dinas ini
            $laporan = LaporanPerjalananDinas::where('perjalanan_dinas_id', $perjalananDinas->id)
                ->where('user_id', $user->id)
                ->first();
        }


        if (!$laporan) {
            // Jika user adalah pelaksana dan belum buat laporan, arahkan ke form create
            if ($perjalananDinas->personil()->where('users.id', $user->id)->exists() && in_array($perjalananDinas->status, ['disetujui', 'selesai'])) {
                return redirect()->route('pegawai.laporan-perjadin.createOrEdit', $perjalananDinas->id);
            }
            return redirect()->route('pegawai.laporan-perjadin.index')->with('error', 'Laporan untuk perjalanan dinas ini tidak ditemukan atau Anda tidak memiliki akses.');
        }

        // Otorisasi tambahan untuk melihat laporan (jika bukan pemilik dan bukan role admin tertentu)
        if ($laporan->user_id != $user->id && !$user->hasAnyRole(['superadmin', 'operator', 'verifikator', 'atasan', 'kepala dinas', 'bendahara'])) {
            abort(403, 'Anda tidak berhak melihat laporan ini.');
        }

        $laporan->load([
            'perjalananDinas.personil',
            'rincianBiaya.sbuItem', // Eager load sbuItem melalui rincianBiaya
            'pelapor'
        ]);
        $estimasiBiaya = $laporan->perjalananDinas->biayaDetails()->with('sbuItem')->get();

        return view('perjalanan_dinas.pegawai.laporan_show', compact('laporan', 'estimasiBiaya'));
    }
}
