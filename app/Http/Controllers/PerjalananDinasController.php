<?php



namespace App\Http\Controllers; // Sesuaikan jika controller Anda di subfolder (misal: App\Http\Controllers\Operator)

use App\Models\PerjalananDinas;
use App\Models\User;
use App\Models\SbuItem;
use App\Models\PerjalananDinasBiaya;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Helpers\TerbilangHelper; // Pastikan ada di app/Helpers/
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class PerjalananDinasController extends Controller
{
    public function __construct()
    {
        // Middleware sebaiknya diatur di file route, contoh:
        // Route::prefix('operator/perjalanan-dinas')...->middleware(['auth', 'role:operator|superadmin']);
        // Jika superadmin juga bisa melakukan aksi operator, tambahkan 'superadmin' di role middleware pada route.
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();
            $query = PerjalananDinas::with(['operator', 'personil'])->latest();

            // Operator hanya melihat pengajuan yang mereka buat, kecuali Super Admin
            if (!$user->hasRole('superadmin')) {
                $query->where('operator_id', $user->id);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('personil_list', fn($pd) => Str::limit($pd->personil->pluck('nama')->implode(', '), 50))
                ->addColumn('tanggal_spt_formatted', fn($pd) => $pd->tanggal_spt ? Carbon::parse($pd->tanggal_spt)->translatedFormat('d M Y') : 'Belum Ditetapkan')
                ->addColumn('tanggal_pelaksanaan', fn($pd) => Carbon::parse($pd->tanggal_mulai)->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($pd->tanggal_selesai)->translatedFormat('d M Y'))
                ->editColumn('status', function ($perjalanan) {
                    $statusText = str_replace('_', ' ', $perjalanan->status);
                    $statusText = ucwords($statusText);
                    $badgeClass = 'bg-gradient-secondary'; // Default
                    if ($perjalanan->status === 'disetujui' || $perjalanan->status === 'selesai') $badgeClass = 'bg-gradient-success';
                    if (Str::contains($perjalanan->status, 'revisi')) $badgeClass = 'bg-gradient-warning';
                    if (Str::contains($perjalanan->status, 'tolak')) $badgeClass = 'bg-gradient-danger';
                    if ($perjalanan->status === 'diproses' || $perjalanan->status === 'menunggu_persetujuan_atasan') $badgeClass = 'bg-gradient-info';
                    return "<span class='badge {$badgeClass}'>{$statusText}</span>";
                })
                ->editColumn('total_estimasi_biaya', fn($pd) => 'Rp ' . number_format($pd->total_estimasi_biaya, 0, ',', '.'))
                ->addColumn('action', function ($perjalanan) {
                    $viewUrl = route('operator.perjalanan-dinas.show', $perjalanan->id);
                    $editUrl = route('operator.perjalanan-dinas.edit', $perjalanan->id);
                    $deleteBtn = '';
                    $editBtn = '';

                    if (in_array($perjalanan->status, ['draft', 'revisi_operator_verifikator', 'revisi_operator_atasan'])) {
                        $editBtn = '<a href="' . $editUrl . '" class="btn btn-sm btn-secondary me-1" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a>';
                        $deleteBtn = '
                        <form action="' . route('operator.perjalanan-dinas.destroy', $perjalanan->id) . '" method="POST" class="d-inline delete-form">
                            ' . csrf_field() . method_field("DELETE") . '
                            <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus"><i class="fas fa-trash"></i></button>
                        </form>';
                    }
                    // Tombol Lihat selalu ada
                    $viewBtn = '<a href="' . $viewUrl . '" class="btn btn-sm btn-info me-1" data-bs-toggle="tooltip" title="Lihat"><i class="fas fa-eye"></i></a>';
                    return '<div class="btn-group">' . $editBtn . $viewBtn . $deleteBtn . '</div>';
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('perjalanan_dinas.operator.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('aktif', true)->orderBy('nama')->get();
        $provinsis = SbuItem::distinct()
                        ->whereNotNull('provinsi_tujuan')
                        ->where('provinsi_tujuan', '!=', '')
                        ->orderBy('provinsi_tujuan')
                        ->pluck('provinsi_tujuan', 'provinsi_tujuan');
        return view('perjalanan_dinas.operator.create', compact('users', 'provinsis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_spt' => 'required|date|before_or_equal:tanggal_mulai',
            'jenis_spt' => 'required|in:dalam_daerah,luar_daerah_dalam_provinsi,luar_daerah_luar_provinsi',
            'jenis_kegiatan' => 'required|string|max:100',
            'tujuan_spt' => 'required|string|max:255',
            'provinsi_tujuan_id' => 'required_if:jenis_spt,luar_daerah_luar_provinsi|nullable|string|max:255', // Hanya wajib jika luar provinsi
            'kota_tujuan_id' => 'nullable|string|max:255',
            'kecamatan_tujuan_id' => 'nullable|string|max:255',
            'desa_tujuan_id' => 'nullable|string|max:255',
            'jarak_km' => 'nullable|integer|min:0',
            'dasar_spt' => 'required|string',
            'uraian_spt' => 'required|string',
            'opsi_transportasi_udara' => 'nullable|in:ya',
            'opsi_transportasi_darat_antar_kota' => 'nullable|in:ya',
            'jumlah_taksi_di_tujuan' => 'nullable|integer|min:0|max:10',
            'alat_angkut_lainnya' => 'nullable|string|max:255',
            'personil_ids' => 'required|array|min:1',
            'personil_ids.*' => 'exists:users,id',
            'tanggal_mulai' => 'required|date|after_or_equal:tanggal_spt',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        if ($validator->fails()) {
            return redirect()->route('operator.perjalanan-dinas.create')
                        ->withErrors($validator)
                        ->withInput();
        }

        DB::beginTransaction();
        try {
            $lama_hari = Carbon::parse($request->tanggal_selesai)->diffInDays(Carbon::parse($request->tanggal_mulai)) + 1;

            $alatAngkutFinal = $request->input('alat_angkut_lainnya', "Kendaraan Dinas/Umum");
            if ($request->filled('opsi_transportasi_udara') && $request->opsi_transportasi_udara == 'ya') {
                $alatAngkutFinal = "Pesawat Udara";
                if($request->filled('alat_angkut_lainnya') && strtolower(trim($request->alat_angkut_lainnya)) !== 'kendaraan dinas/umum' && !empty(trim($request->alat_angkut_lainnya))){
                    $alatAngkutFinal .= " / " . trim($request->alat_angkut_lainnya);
                }
            } elseif ($request->filled('opsi_transportasi_darat_antar_kota') && $request->opsi_transportasi_darat_antar_kota == 'ya') {
                $alatAngkutFinal = "Kendaraan Umum Antar Kota";
                 if($request->filled('alat_angkut_lainnya') && strtolower(trim($request->alat_angkut_lainnya)) !== 'kendaraan dinas/umum' && !empty(trim($request->alat_angkut_lainnya))){
                    $alatAngkutFinal .= " / " . trim($request->alat_angkut_lainnya);
                }
            }

            // Tentukan provinsi_tujuan_id di backend
            $provinsiTujuanUntukSimpan = $request->provinsi_tujuan_id;
            if ($request->jenis_spt == 'dalam_daerah' || $request->jenis_spt == 'luar_daerah_dalam_provinsi') {
                $provinsiTujuanUntukSimpan = 'RIAU';
            }

            $perjalanan = PerjalananDinas::create([
                'nomor_spt' => null, // Akan diisi oleh Atasan
                'tanggal_spt' => $request->tanggal_spt,
                'jenis_spt' => $request->jenis_spt,
                'jenis_kegiatan' => $request->jenis_kegiatan,
                'tujuan_spt' => $request->tujuan_spt,
                'provinsi_tujuan_id' => $provinsiTujuanUntukSimpan,
                'kota_tujuan_id' => ($request->jenis_spt == 'dalam_daerah' && strtoupper($provinsiTujuanUntukSimpan) == 'RIAU') ? 'SIAK' : $request->kota_tujuan_id,
                // Simpan kecamatan, desa, jarak jika Anda menambahkannya ke model PerjalananDinas
                // 'kecamatan_tujuan_id' => $request->kecamatan_tujuan_id,
                // 'desa_tujuan_id' => $request->desa_tujuan_id,
                // 'jarak_km' => $request->jarak_km,
                'dasar_spt' => $request->dasar_spt,
                'uraian_spt' => $request->uraian_spt,
                'alat_angkut' => $alatAngkutFinal,
                'lama_hari' => $lama_hari,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'status' => 'diproses',
                'operator_id' => Auth::id(),
                'total_estimasi_biaya' => 0, // Akan diupdate
            ]);

            $perjalanan->personil()->attach($request->personil_ids);

            // --- Kalkulasi Estimasi Biaya Otomatis ---
            $totalEstimasiKeseluruhan = 0;
            $detailBiayaUntukSimpan = [];
            $personilsBerangkat = User::whereIn('id', $request->personil_ids)->get();

            foreach ($personilsBerangkat as $personil) {
                $tingkatSbuPersonil = $this->getSbuTingkatUntukPersonil($personil);
                $isDiklat = strtolower($request->jenis_kegiatan) == 'diklat';
                $logPrefix = "Kalkulasi Biaya Perjadin (ID sementara {$perjalanan->id}), Personil ID {$personil->id}, Tingkat SBU {$tingkatSbuPersonil}: ";

                // 1. UANG HARIAN
                $sbuUHQuery = SbuItem::where('kategori_biaya', 'UANG_HARIAN')->where('provinsi_tujuan', $provinsiTujuanUntukSimpan);
                if ($isDiklat) { $sbuUHQuery->where('uraian_biaya', 'like', '%Diklat%'); } else { $sbuUHQuery->where('uraian_biaya', 'not like', '%Diklat%'); }
                if ($request->jenis_spt == 'dalam_daerah') { $sbuUHQuery->where('tipe_perjalanan', 'DALAM_KABUPATEN_LEBIH_8_JAM'); } else { $sbuUHQuery->where('tipe_perjalanan', 'LUAR_DAERAH_LUAR_KABUPATEN'); }
                $sbuUangHarian = $sbuUHQuery->where(fn($q) => $q->where('tingkat_pejabat_atau_golongan', $tingkatSbuPersonil)->orWhere('tingkat_pejabat_atau_golongan', 'Semua'))
                                      ->orderByRaw("CASE WHEN tingkat_pejabat_atau_golongan = ? THEN 0 ELSE 1 END", [$tingkatSbuPersonil])->first();
                if ($sbuUangHarian) {
                    $subtotal = $sbuUangHarian->besaran_biaya * $lama_hari;
                    $detailBiayaUntukSimpan[] = new PerjalananDinasBiaya([
                        'sbu_item_id' => $sbuUangHarian->id, 'user_id_terkait' => $personil->id,
                        'deskripsi_biaya' => $sbuUangHarian->uraian_biaya . ($isDiklat ? " (Diklat)" : ""),
                        'jumlah_personil_terkait' => 1, 'jumlah_hari_terkait' => $lama_hari,
                        'jumlah_unit' => $lama_hari, 'harga_satuan' => $sbuUangHarian->besaran_biaya, 'subtotal_biaya' => $subtotal,
                    ]);
                    $totalEstimasiKeseluruhan += $subtotal;
                } else { Log::warning($logPrefix . "SBU Uang Harian tidak ditemukan. Prov: {$provinsiTujuanUntukSimpan}, Keg: {$request->jenis_kegiatan}, Diklat: " . ($isDiklat ? 'Ya':'Tidak'));}

                // 2. BIAYA PENGINAPAN
                $jumlahMalamInap = max(0, $lama_hari - 1);
                if ($jumlahMalamInap > 0 && !$isDiklat) {
                    $sbuPenginapanQuery = SbuItem::where('kategori_biaya', 'PENGINAPAN')->where('provinsi_tujuan', $provinsiTujuanUntukSimpan);
                    if($request->jenis_spt == 'dalam_daerah' && strtoupper($provinsiTujuanUntukSimpan) == 'RIAU'){ $sbuPenginapanQuery->where('kota_tujuan', 'SIAK')->where('tipe_perjalanan', 'DALAM_KABUPATEN_LEBIH_8_JAM'); }
                    else if ($request->jenis_spt != 'dalam_daerah'){ $sbuPenginapanQuery->where('tipe_perjalanan', 'LUAR_DAERAH_LUAR_KABUPATEN'); }
                    else { $sbuPenginapanQuery->whereRaw('1=0'); } // Kondisi agar tidak ada hasil jika jenis_spt tidak sesuai

                    $sbuPenginapan = $sbuPenginapanQuery->where(fn($q) => $q->where('tingkat_pejabat_atau_golongan', $tingkatSbuPersonil)->orWhere('tingkat_pejabat_atau_golongan', 'Semua'))
                                           ->orderByRaw("CASE WHEN tingkat_pejabat_atau_golongan = ? THEN 0 ELSE 1 END", [$tingkatSbuPersonil])->first();
                    if ($sbuPenginapan) {
                        $subtotal = $sbuPenginapan->besaran_biaya * $jumlahMalamInap;
                        $detailBiayaUntukSimpan[] = new PerjalananDinasBiaya([
                            'sbu_item_id' => $sbuPenginapan->id, 'user_id_terkait' => $personil->id,
                            'deskripsi_biaya' => $sbuPenginapan->uraian_biaya,
                            'jumlah_personil_terkait' => 1, 'jumlah_hari_terkait' => $jumlahMalamInap,
                            'jumlah_unit' => $jumlahMalamInap, 'harga_satuan' => $sbuPenginapan->besaran_biaya, 'subtotal_biaya' => $subtotal,
                        ]);
                        $totalEstimasiKeseluruhan += $subtotal;
                    } else { Log::warning($logPrefix . "SBU Penginapan tidak ditemukan. Prov: {$provinsiTujuanUntukSimpan}."); }
                }

                // 3. BIAYA TRANSPORTASI UDARA (PESAWAT)
                if ($request->jenis_spt == 'luar_daerah_luar_provinsi' && $request->filled('opsi_transportasi_udara') && $request->opsi_transportasi_udara == 'ya') {
                    $sbuPesawat = SbuItem::where('kategori_biaya', 'TRANSPORTASI_UDARA')->where('provinsi_tujuan', $provinsiTujuanUntukSimpan)->where('tingkat_pejabat_atau_golongan', 'EKONOMI')->first();
                    if($sbuPesawat){ $subtotal = $sbuPesawat->besaran_biaya; $detailBiayaUntukSimpan[] = new PerjalananDinasBiaya(['sbu_item_id' => $sbuPesawat->id, 'user_id_terkait' => $personil->id, 'deskripsi_biaya' => $sbuPesawat->uraian_biaya, 'jumlah_personil_terkait' => 1, 'jumlah_hari_terkait' => 0, 'jumlah_unit' => 1, 'harga_satuan' => $sbuPesawat->besaran_biaya, 'subtotal_biaya' => $subtotal,]); $totalEstimasiKeseluruhan += $subtotal; }
                    else { Log::warning($logPrefix . "SBU Pesawat tidak ditemukan. Prov Tujuan: {$provinsiTujuanUntukSimpan}."); }
                }

                // 4. BIAYA TRANSPORTASI DARAT (TAKSI) DI KOTA TUJUAN
                $jumlahTaksi = intval($request->input('jumlah_taksi_di_tujuan', 0));
                if ($jumlahTaksi > 0 && ($request->jenis_spt == 'luar_daerah_dalam_provinsi' || $request->jenis_spt == 'luar_daerah_luar_provinsi')) {
                    $sbuTaksi = SbuItem::where('kategori_biaya', 'TRANSPORTASI_DARAT_TAKSI')->where('provinsi_tujuan', $provinsiTujuanUntukSimpan)->first();
                    if ($sbuTaksi) { $subtotal = $sbuTaksi->besaran_biaya * $jumlahTaksi; $detailBiayaUntukSimpan[] = new PerjalananDinasBiaya(['sbu_item_id' => $sbuTaksi->id, 'user_id_terkait' => $personil->id, 'deskripsi_biaya' => $sbuTaksi->uraian_biaya . " ({$jumlahTaksi} kali)", 'jumlah_personil_terkait' => 1, 'jumlah_hari_terkait' => 0, 'jumlah_unit' => $jumlahTaksi, 'harga_satuan' => $sbuTaksi->besaran_biaya, 'subtotal_biaya' => $subtotal,]); $totalEstimasiKeseluruhan += $subtotal; }
                    else { Log::warning($logPrefix . "SBU Taksi tidak ditemukan. Prov Tujuan: {$provinsiTujuanUntukSimpan}."); }
                }

                // 5. UANG REPRESENTASI
                if (!$isDiklat) {
                    $tipePerjalananRep = ($request->jenis_spt == 'dalam_daerah') ? 'DALAM_KABUPATEN_LEBIH_8_JAM' : (($request->jenis_spt != 'dalam_daerah') ? 'LUAR_DAERAH_LUAR_KABUPATEN' : null);
                    if ($tipePerjalananRep) {
                        $sbuRepresentasi = SbuItem::where('kategori_biaya', 'REPRESENTASI')->where('tipe_perjalanan', $tipePerjalananRep)->where('tingkat_pejabat_atau_golongan', $tingkatSbuPersonil)->first();
                        if ($sbuRepresentasi) { $subtotal = $sbuRepresentasi->besaran_biaya * $lama_hari; $detailBiayaUntukSimpan[] = new PerjalananDinasBiaya(['sbu_item_id' => $sbuRepresentasi->id, 'user_id_terkait' => $personil->id, 'deskripsi_biaya' => $sbuRepresentasi->uraian_biaya, 'jumlah_personil_terkait' => 1, 'jumlah_hari_terkait' => $lama_hari, 'jumlah_unit' => $lama_hari, 'harga_satuan' => $sbuRepresentasi->besaran_biaya, 'subtotal_biaya' => $subtotal,]); $totalEstimasiKeseluruhan += $subtotal; }
                    }
                }

                // 6. TRANSPORTASI ANTAR KABUPATEN DALAM PROVINSI (DARI SIAK)
                if ($request->jenis_spt == 'luar_daerah_dalam_provinsi' && strtoupper($provinsiTujuanUntukSimpan) == 'RIAU' && $request->filled('kota_tujuan_id') && strtoupper($request->kota_tujuan_id) != 'SIAK' && $request->filled('opsi_transportasi_darat_antar_kota') && $request->opsi_transportasi_darat_antar_kota == 'ya' && !($request->filled('opsi_transportasi_udara') && $request->opsi_transportasi_udara == 'ya')) {
                    $sbuAntarKab = SbuItem::where('kategori_biaya', 'TRANSPORTASI_ANTAR_KABUPATEN_PROVINSI')->where('provinsi_tujuan', 'RIAU')->where('kota_tujuan', strtoupper($request->kota_tujuan_id))->first();
                    if($sbuAntarKab){ $subtotal = $sbuAntarKab->besaran_biaya; $detailBiayaUntukSimpan[] = new PerjalananDinasBiaya(['sbu_item_id' => $sbuAntarKab->id, 'user_id_terkait' => $personil->id, 'deskripsi_biaya' => $sbuAntarKab->uraian_biaya, 'jumlah_personil_terkait' => 1, 'jumlah_hari_terkait' => 0, 'jumlah_unit' => 1, 'harga_satuan' => $sbuAntarKab->besaran_biaya, 'subtotal_biaya' => $subtotal,]); $totalEstimasiKeseluruhan += $subtotal; }
                    else { Log::warning($logPrefix . "SBU Trans Antar Kab tidak ditemukan. Kota Tujuan Riau: {$request->kota_tujuan_id}."); }
                }

                // 7. TRANSPORTASI KECAMATAN/DESA (DALAM KABUPATEN SIAK)
                if ($request->jenis_spt == 'dalam_daerah' && strtoupper($provinsiTujuanUntukSimpan) == 'RIAU' && ($request->filled('kecamatan_tujuan_id') || $request->filled('jarak_km')) && !($request->filled('opsi_transportasi_udara') && $request->opsi_transportasi_udara == 'ya') && !($request->filled('opsi_transportasi_darat_antar_kota') && $request->opsi_transportasi_darat_antar_kota == 'ya')) {
                    $sbuTransInternalQuery = SbuItem::where('kategori_biaya', 'TRANSPORTASI_KECAMATAN_DESA')->where('provinsi_tujuan', 'RIAU')->where('kota_tujuan', 'SIAK');
                    if ($request->filled('kecamatan_tujuan_id') && $request->filled('desa_tujuan_id')) { $sbuTransInternalQuery->where('kecamatan_tujuan', $request->kecamatan_tujuan_id)->where('desa_tujuan', $request->desa_tujuan_id); }
                    elseif ($request->filled('kecamatan_tujuan_id')) { $sbuTransInternalQuery->where('kecamatan_tujuan', $request->kecamatan_tujuan_id)->where(fn($q) => $q->whereNull('desa_tujuan')->orWhere('desa_tujuan','')); }
                    elseif ($request->filled('jarak_km')) { $jarak = intval($request->jarak_km); $sbuTransInternalQuery->where('jarak_km_min', '<=', $jarak)->where('jarak_km_max', '>=', $jarak); }
                    else { $sbuTransInternalQuery->whereRaw('1=0'); }
                    $sbuTransportInternal = $sbuTransInternalQuery->first();
                    if($sbuTransportInternal){ $subtotal = $sbuTransportInternal->besaran_biaya; $detailBiayaUntukSimpan[] = new PerjalananDinasBiaya(['sbu_item_id' => $sbuTransportInternal->id, 'user_id_terkait' => $personil->id, 'deskripsi_biaya' => $sbuTransportInternal->uraian_biaya, 'jumlah_personil_terkait' => 1, 'jumlah_hari_terkait' => 0, 'jumlah_unit' => 1, 'harga_satuan' => $sbuTransportInternal->besaran_biaya, 'subtotal_biaya' => $subtotal,]); $totalEstimasiKeseluruhan += $subtotal; }
                    else { Log::warning($logPrefix . "SBU Trans Internal Siak tidak ditemukan. Kec: {$request->kecamatan_tujuan_id}, Desa: {$request->desa_tujuan_id}, Jarak: {$request->jarak_km}."); }
                }

            } // End foreach personilsBerangkat

            if (!empty($detailBiayaUntukSimpan)) {
                $perjalanan->biayaDetails()->saveMany($detailBiayaUntukSimpan);
            }
            $perjalanan->total_estimasi_biaya = $totalEstimasiKeseluruhan;
            $perjalanan->save();

            DB::commit();
            return redirect()->route('operator.perjalanan-dinas.index')->with('success', 'Pengajuan perjalanan dinas berhasil dibuat. Estimasi Biaya: Rp ' . number_format($totalEstimasiKeseluruhan,0,',','.'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saat menyimpan perjalanan dinas: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Gagal membuat pengajuan: Terjadi kesalahan internal. Detail: '. $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PerjalananDinas $perjalananDinas)
    {
        // $this->authorize('view', $perjalananDinas); // Implement Policy
        $perjalananDinas->load(['personil', 'operator', 'verifikator', 'atasan', 'biayaDetails.sbuItem', 'biayaDetails.userTerkait']);
        return view('perjalanan_dinas.operator.show', compact('perjalananDinas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    // app/Http/Controllers/PerjalananDinasController.php

public function edit(PerjalananDinas $perjalananDinas) // Laravel akan otomatis inject $perjalananDinas berdasarkan ID di URL
{
    // Implementasi otorisasi (misalnya menggunakan Policy)
    // $this->authorize('update', $perjalananDinas);

    if (!in_array($perjalananDinas->status, ['draft', 'revisi_operator_verifikator', 'revisi_operator_atasan'])) {
        return redirect()->route('operator.perjalanan-dinas.index')->with('error', 'Pengajuan ini tidak dapat diedit lagi.');
    }

    $users = User::where('aktif', true)->orderBy('nama')->get();
    $provinsis = SbuItem::distinct()
                    ->whereNotNull('provinsi_tujuan')
                    ->where('provinsi_tujuan', '!=', '')
                    ->orderBy('provinsi_tujuan')
                    ->pluck('provinsi_tujuan', 'provinsi_tujuan');
    $selectedPersonilIds = $perjalananDinas->personil->pluck('id')->toArray();

    // Menentukan nilai awal untuk field transportasi dari data $perjalananDinas
    $opsiUdaraTerpilih = Str::contains(strtolower($perjalananDinas->alat_angkut ?? ''), 'pesawat');
    $opsiTransportasiDaratAntarKotaTerpilih = Str::contains(strtolower($perjalananDinas->alat_angkut ?? ''), 'antar kota');

    $alatAngkutLainnya = $perjalananDinas->alat_angkut;
    if ($opsiUdaraTerpilih) {
        // Jika "Pesawat Udara / Kendaraan Lain", ambil "Kendaraan Lain"
        // Jika hanya "Pesawat Udara", set default untuk input teks
        $parts = explode('/', $perjalananDinas->alat_angkut);
        $nonPesawatParts = array_filter($parts, function($part) {
            return !Str::contains(strtolower(trim($part)), 'pesawat');
        });
        $alatAngkutLainnya = !empty($nonPesawatParts) ? trim(implode(' / ', $nonPesawatParts)) : 'Kendaraan Dinas/Umum';
        if (empty(trim($alatAngkutLainnya)) && count($parts) === 1 && Str::contains(strtolower(trim($parts[0])), 'pesawat')) {
             $alatAngkutLainnya = 'Kendaraan Dinas/Umum'; // Jika hanya Pesawat Udara
        }
    } elseif ($opsiTransportasiDaratAntarKotaTerpilih) {
        // Logika serupa jika perlu memisahkan dari "Kendaraan Umum Antar Kota / ..."
         $parts = explode('/', $perjalananDinas->alat_angkut);
        $nonAntarKotaParts = array_filter($parts, function($part) {
            return !Str::contains(strtolower(trim($part)), 'antar kota');
        });
        $alatAngkutLainnya = !empty($nonAntarKotaParts) ? trim(implode(' / ', $nonAntarKotaParts)) : 'Kendaraan Dinas/Umum';
         if (empty(trim($alatAngkutLainnya)) && count($parts) === 1 && Str::contains(strtolower(trim($parts[0])), 'antar kota')) {
             $alatAngkutLainnya = 'Kendaraan Dinas/Umum';
        }
    }


    // Ambil data yang mungkin sudah diinput sebelumnya jika ada kolomnya di perjalanan_dinas
    // Jika kolom ini tidak ada di tabel perjalanan_dinas, Anda tidak bisa mengambilnya dari $perjalananDinas->nama_kolom
    // Anda harus mengambilnya dari $request->old('nama_kolom') saja di view.
    $jumlahTaksiDiTujuan = old('jumlah_taksi_di_tujuan', $perjalananDinas->jumlah_taksi_di_tujuan ?? 0); // Asumsi ada kolom 'jumlah_taksi_di_tujuan' di model
    $kecamatanTujuanId = old('kecamatan_tujuan_id', $perjalananDinas->kecamatan_tujuan_id ?? ''); // Asumsi ada kolom
    $desaTujuanId = old('desa_tujuan_id', $perjalananDinas->desa_tujuan_id ?? '');           // Asumsi ada kolom
    $jarakKm = old('jarak_km', $perjalananDinas->jarak_km ?? '');                           // Asumsi ada kolom

    return view('perjalanan_dinas.operator.edit', compact(
        'perjalananDinas',
        'users',
        'provinsis',
        'selectedPersonilIds',
        'opsiUdaraTerpilih',
        'opsiTransportasiDaratAntarKotaTerpilih', // Kirim ini juga
        'alatAngkutLainnya',
        'jumlahTaksiDiTujuan',
        'kecamatanTujuanId',
        'desaTujuanId',
        'jarakKm'
    ));
}
    

    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PerjalananDinas $perjalananDinas)
    {
        // $this->authorize('update', $perjalananDinas); // Implement Policy
        if (!in_array($perjalananDinas->status, ['draft', 'revisi_operator_verifikator', 'revisi_operator_atasan'])) {
            return redirect()->route('operator.perjalanan-dinas.index')->with('error', 'Pengajuan ini tidak dapat diedit lagi.');
        }

        // Validasi (sama seperti store, tapi sesuaikan untuk update jika perlu)
        $validator = Validator::make($request->all(), [ /* ... Salin validasi dari store() ... */ ]);
        if ($validator->fails()) { return redirect()->route('operator.perjalanan-dinas.edit', $perjalananDinas->id)->withErrors($validator)->withInput(); }

        DB::beginTransaction();
        try {
            $lama_hari = Carbon::parse($request->tanggal_selesai)->diffInDays(Carbon::parse($request->tanggal_mulai)) + 1;
            $alatAngkutFinal = $request->input('alat_angkut_lainnya', "Kendaraan Dinas/Umum");
            if ($request->filled('opsi_transportasi_udara') && $request->opsi_transportasi_udara == 'ya') { /* ... logika alatAngkutFinal ... */ }
            // ... (Logika alatAngkutFinal sama seperti store)

            $provinsiTujuan = ($request->jenis_spt == 'dalam_daerah' || $request->jenis_spt == 'luar_daerah_dalam_provinsi') ? 'RIAU' : $request->provinsi_tujuan_id;

            // Data yang diupdate untuk PerjalananDinas
            $perjalananDinasData = $request->except(['_token', '_method', 'personil_ids', 'total_estimasi_biaya', 'nomor_spt']);
            $perjalananDinasData['lama_hari'] = $lama_hari;
            $perjalananDinasData['alat_angkut'] = $alatAngkutFinal;
            $perjalananDinasData['provinsi_tujuan_id'] = $provinsiTujuan;
            $perjalananDinasData['status'] = 'diproses'; // Kembalikan ke diproses
            $perjalananDinasData['catatan_verifikator'] = null;
            $perjalananDinasData['catatan_atasan'] = null;

            $perjalananDinas->update($perjalananDinasData);
            $perjalananDinas->personil()->sync($request->personil_ids);

            // Hapus detail biaya lama & Hitung ulang
            $perjalananDinas->biayaDetails()->delete();
            $totalEstimasiKeseluruhan = 0;
            $detailBiayaUntukSimpan = [];
            $personilsBerangkat = User::whereIn('id', $request->personil_ids)->get();
            foreach ($personilsBerangkat as $personil) {
                // !!! SALIN DAN TEMPEL SELURUH BLOK PERHITUNGAN BIAYA (Uang Harian s/d Transportasi Internal) DARI METHOD store() KE SINI !!!
                // Ini sangat penting agar estimasi biaya terupdate dengan benar setelah edit.
                $tingkatSbuPersonil = $this->getSbuTingkatUntukPersonil($personil);
                $isDiklat = strtolower($request->jenis_kegiatan) == 'diklat';
                $logPrefix = "Update Kalkulasi Biaya Perjadin ID {$perjalananDinas->id}, Personil ID {$personil->id}, Tingkat SBU {$tingkatSbuPersonil}: ";
                // Uang Harian...
                // Penginapan...
                // Udara...
                // Taksi...
                // Representasi...
                // Antar Kab...
                // Internal Siak...
            }
            if (!empty($detailBiayaUntukSimpan)) {
                $perjalananDinas->biayaDetails()->saveMany($detailBiayaUntukSimpan);
            }
            $perjalananDinas->total_estimasi_biaya = $totalEstimasiKeseluruhan;
            $perjalananDinas->save();

            DB::commit();
            return redirect()->route('operator.perjalanan-dinas.index')->with('success', 'Pengajuan perjalanan dinas berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saat update perjalanan dinas: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Gagal memperbarui pengajuan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PerjalananDinas $perjalananDinas)
    {
        // $this->authorize('delete', $perjalananDinas); // Implement Policy
         if (!in_array($perjalananDinas->status, ['draft', 'revisi_operator_verifikator', 'revisi_operator_atasan'])) {
            return redirect()->route('operator.perjalanan-dinas.index')->with('error', 'Pengajuan ini tidak dapat dihapus karena statusnya.');
        }
        DB::beginTransaction();
        try {
            $perjalananDinas->personil()->detach();
            $perjalananDinas->biayaDetails()->delete();
            $perjalananDinas->delete();
            DB::commit();
            return redirect()->route('operator.perjalanan-dinas.index')->with('success', 'Pengajuan perjalanan dinas berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saat hapus perjalanan dinas: " . $e->getMessage());
            return redirect()->route('operator.perjalanan-dinas.index')->with('error', 'Gagal menghapus pengajuan.');
        }
    }

    // --- Helper Functions ---
    private function getSbuTingkatUntukPersonil(User $user): string
    {
        $jabatan = strtoupper($user->jabatan ?? '');
        $golonganFull = strtoupper($user->gol ?? '');
        $golonganAngkaRomawi = '';
        if (!empty($golonganFull)) {
            $parts = explode('/', $golonganFull);
            $golonganAngkaRomawi = trim($parts[0]);
        }

        if (Str::contains($jabatan, ['KEPALA DINAS', 'BUPATI', 'WAKIL BUPATI', 'KETUA DPRD', 'PIMPINAN DPRD']) || Str::contains($jabatan, 'ESELON I') || preg_match('/ESELON\s*I\b/i', $jabatan) ) {
            return 'KEPALA_DAERAH_ESELON_I';
        }
        if (Str::contains($jabatan, 'ANGGOTA DPRD') || Str::contains($jabatan, 'ESELON II') || preg_match('/ESELON\s*II\b/i', $jabatan) ) {
            return 'ESELON_II';
        }
        if (Str::contains($jabatan, 'ESELON III') || preg_match('/ESELON\s*III\b/i', $jabatan) ) {
            return 'ESELON_III_GOL_IV';
        }
        if ($golonganAngkaRomawi === 'IV') { // Jika Gol IV tanpa eselon III eksplisit, masih masuk kategori ini
            return 'ESELON_III_GOL_IV';
        }
        if (Str::contains($jabatan, 'ESELON IV') || preg_match('/ESELON\s*IV\b/i', $jabatan) ) {
            return 'ESELON_IV_GOL_III';
        }
        if ($golonganAngkaRomawi === 'III') { // Jika Gol III tanpa eselon IV eksplisit
            return 'ESELON_IV_GOL_III';
        }
        if ($golonganAngkaRomawi === 'II' || $golonganAngkaRomawi === 'I' || empty($golonganFull) ) {
            return 'GOL_II_I_NON_ASN';
        }
        Log::warning("Tidak ada mapping SBU tingkat/golongan yang cocok untuk user ID: {$user->id}, Jabatan: {$user->jabatan}, Golongan: {$user->gol}. Menggunakan default GOL_II_I_NON_ASN.");
        return 'GOL_II_I_NON_ASN';
    }

    private function getRomanMonth($monthNumber) {
        $map = array(1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII');
        return $map[intval($monthNumber)] ?? '';
    }
}