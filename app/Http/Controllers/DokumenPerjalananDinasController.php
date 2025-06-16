<?php

namespace App\Http\Controllers;

use App\Models\PerjalananDinas;
use App\Models\User; // Mungkin diperlukan untuk mengambil data personil secara eksplisit jika ada kasus khusus
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Helpers\TerbilangHelper; // Pastikan namespace dan file helper ini benar
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; // Untuk mengecek keberadaan file template

class DokumenPerjalananDinasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Menampilkan daftar dokumen perjalanan dinas yang bisa diunduh.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();
            $query = PerjalananDinas::with(['operator', 'personil'])
                ->whereIn('status', ['disetujui', 'selesai']);

            if ($user->hasRole('pegawai') && !$user->hasAnyRole(['operator', 'superadmin', 'atasan', 'verifikator'])) {
                $query->whereHas('personil', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('nomor_spt_display', fn($pd) => $pd->nomor_spt ?? '<em class="text-muted">Belum Ada</em>') // Handle jika nomor SPT belum ada
                ->addColumn('tanggal_spt_formatted', fn($pd) => $pd->tanggal_spt ? Carbon::parse($pd->tanggal_spt)->translatedFormat('d M Y') : '-')
                ->addColumn('tujuan_spt_display', fn($pd) => $pd->tujuan_spt)
                ->addColumn('personil_list', fn($pd) => Str::limit($pd->personil->pluck('nama')->implode(', '), 50))
                ->addColumn('tanggal_pelaksanaan', fn($pd) => Carbon::parse($pd->tanggal_mulai)->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($pd->tanggal_selesai)->translatedFormat('d M Y'))
                ->editColumn('status', function ($perjalanan) {
                    $statusText = str_replace('_', ' ', $perjalanan->status);
                    $statusText = ucwords($statusText);
                    $badgeClass = 'bg-gradient-secondary';
                    if ($perjalanan->status === 'disetujui') $badgeClass = 'bg-gradient-success';
                    if ($perjalanan->status === 'selesai') $badgeClass = 'bg-gradient-primary';
                    if (Str::contains($perjalanan->status, 'revisi')) $badgeClass = 'bg-gradient-warning';
                    if (Str::contains($perjalanan->status, 'tolak')) $badgeClass = 'bg-gradient-danger';
                    return "<span class='badge {$badgeClass}'>{$statusText}</span>";
                })
                ->addColumn('action', function ($perjalanan) {
                    if (!in_array($perjalanan->status, ['disetujui', 'selesai'])) {
                        return '<span class="badge bg-gradient-warning">Menunggu Penyelesaian Dokumen</span>';
                    }
                    if (empty($perjalanan->nomor_spt)) { // Jika nomor SPT belum ada (seharusnya tidak terjadi jika status disetujui/selesai)
                        return '<span class="badge bg-gradient-secondary">Nomor SPT Belum Terbit</span>';
                    }

                    $sptPdfUrl = route('dokumen.spt.download', ['perjalananDinas' => $perjalanan->id, 'format' => 'pdf']);
                    $sptWordUrl = route('dokumen.spt.download', ['perjalananDinas' => $perjalanan->id, 'format' => 'word']);
                    $sppdPdfUrl = route('dokumen.sppd.download', ['perjalananDinas' => $perjalanan->id, 'format' => 'pdf']);

                    $sppdWordButtons = '';
                    if ($perjalanan->personil->count() == 1) {
                        $p = $perjalanan->personil->first();
                        $url = route('dokumen.sppd.download', ['perjalananDinas' => $perjalanan->id, 'format' => 'word', 'personil_id' => $p->id]);
                        $sppdWordButtons = '<a href="' . $url . '" class="btn btn-warning btn-sm" target="_blank" data-bs-toggle="tooltip" title="Download SPPD Word ' . htmlspecialchars($p->nama) . '"><i class="fas fa-file-word"></i> SPPD DOCX</a>';
                    } elseif ($perjalanan->personil->count() > 1) {
                        $sppdWordButtons = '<div class="btn-group btn-group-sm">
                                              <button type="button" class="btn btn-warning dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle="tooltip" title="Download SPPD Word per Personil">
                                                <i class="fas fa-file-word"></i> SPPD DOCX
                                              </button>
                                              <ul class="dropdown-menu">';
                        foreach ($perjalanan->personil as $p) {
                            $url = route('dokumen.sppd.download', ['perjalananDinas' => $perjalanan->id, 'format' => 'word', 'personil_id' => $p->id]);
                            $sppdWordButtons .= '<li><a class="dropdown-item" href="' . $url . '" target="_blank">Untuk: ' . htmlspecialchars($p->nama) . '</a></li>';
                        }
                        $sppdWordButtons .= '</ul></div>';
                    }


                    return '
                        <div class="btn-group btn-group-sm mb-1" role="group" aria-label="SPT Actions">
                            <a href="' . $sptPdfUrl . '" class="btn btn-danger" target="_blank" data-bs-toggle="tooltip" title="Download SPT (PDF)"><i class="fas fa-file-pdf"></i> SPT PDF</a>
                            <a href="' . $sptWordUrl . '" class="btn btn-primary" target="_blank" data-bs-toggle="tooltip" title="Download SPT (Word)"><i class="fas fa-file-word"></i> SPT DOCX</a>
                        </div>
                        <div class="btn-group btn-group-sm" role="group" aria-label="SPPD Actions">
                            <a href="' . $sppdPdfUrl . '" class="btn btn-danger" target="_blank" data-bs-toggle="tooltip" title="Download SPPD (PDF)"><i class="fas fa-file-pdf"></i> SPPD PDF</a>
                            ' . $sppdWordButtons . '
                        </div>
                    ';
                })
                ->rawColumns(['action', 'status', 'nomor_spt_display'])
                ->make(true);
        }
        return view('perjalanan_dinas.dokumen.index');
    }

    /**
     * Menghasilkan dan mengunduh dokumen SPT.
     */
    public function downloadSPT(PerjalananDinas $perjalananDinas, $format = 'pdf')
    {
        // ... (Otorisasi dan data Kadis sama seperti sebelumnya) ...
        $user = Auth::user();
        $isPersonil = $perjalananDinas->personil()->where('users.id', $user->id)->exists();
        if (!$isPersonil && !$user->hasAnyRole(['operator', 'superadmin', 'atasan', 'verifikator'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses dokumen SPT ini.');
        }
        if (!in_array($perjalananDinas->status, ['disetujui', 'selesai'])) {
            return redirect()->back()->with('error', 'SPT belum bisa diunduh karena status perjalanan dinas belum disetujui/selesai.');
        }

        $namaKadis = config('constants.kadis.nama', 'ROMY LESMANA DERMAWAN, AP, M.Si');
        $pangkatKadis = config('constants.kadis.pangkat', 'Pembina Utama Muda (IV/c)');
        $nipKadis = config('constants.kadis.nip', '197402021993031004');

        $dasarSPTListForPdf = [];
        $dasarSPTForWord = '';
        if (!empty($perjalananDinas->dasar_spt)) {
            $dasarLines = preg_split('/\r\n|\r|\n/', $perjalananDinas->dasar_spt);
            $tempDasarWordItems = [];
            foreach ($dasarLines as $idx => $line) {
                $trimmedLine = trim($line);
                if (!empty($trimmedLine)) {
                    $dasarContent = $trimmedLine;
                    $nomorList = $idx + 1;
                    if (preg_match('/^(\d+)\.?\s*(.*)/', $trimmedLine, $matches)) {
                        $nomorList = $matches[1];
                        $dasarContent = $matches[2];
                        $tempDasarWordItems[] = $nomorList . '. ' . htmlspecialchars($dasarContent);
                    } else {
                        $tempDasarWordItems[] = htmlspecialchars($dasarContent);
                    }
                    $dasarSPTListForPdf[] = $dasarContent;
                }
            }
            $dasarSPTForWord = implode('<w:br/>', $tempDasarWordItems);
        }

        $dataForPdf = [ /* ... (sama seperti sebelumnya) ... */
            'perjalananDinas' => $perjalananDinas->load('personil'),
            'namaKadis' => $namaKadis,
            'pangkatKadis' => $pangkatKadis,
            'nipKadis' => $nipKadis,
            'dasarSPTList' => $dasarSPTListForPdf,
            'terbilangHelper' => new TerbilangHelper(),
        ];

        $nomorSPTClean = Str::slug($perjalananDinas->nomor_spt, '-');
        $fileNameBase = 'SPT-' . $nomorSPTClean;

        if (strtolower($format) === 'pdf') {
            // ... (Logika PDF sama seperti sebelumnya) ...
            try {
                Pdf::setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'serif']);
                $pdf = Pdf::loadView('pdf.spt', $dataForPdf);
                return $pdf->download($fileNameBase . '.pdf');
            } catch (\Exception $e) {
                Log::error("Error generating SPT PDF: " . $e->getMessage() . " File: " . $e->getFile() . " Line: " . $e->getLine());
                return redirect()->back()->with('error', 'Gagal membuat file PDF SPT. Silakan cek log untuk detail.');
            }
        } elseif (strtolower($format) === 'word') {
            try {
                $templatePath = storage_path('app/templates/template_spt.docx');
                if (!Storage::disk('local')->exists('templates/template_spt.docx')) {
                    Log::error('Template SPT Word tidak ditemukan di: ' . $templatePath);
                    return redirect()->back()->with('error', 'Template dokumen Word SPT tidak ditemukan.');
                }

                $templateProcessor = new TemplateProcessor($templatePath);

                // Mengisi placeholder tunggal
                $templateProcessor->setValue('NOMOR_SPT', htmlspecialchars($perjalananDinas->nomor_spt ?? '-'));
                $templateProcessor->setValue('TANGGAL_SPT_DITETAPKAN', htmlspecialchars($perjalananDinas->tanggal_spt ? Carbon::parse($perjalananDinas->tanggal_spt)->translatedFormat('d F Y') : '-')); // Sesuai gambar Anda "20 May 2025"
                $templateProcessor->setValue('DASAR_SPT', $dasarSPTForWord);

                // Mengisi blok personil
                if ($perjalananDinas->personil->count() > 0) {
                    $templateProcessor->cloneBlock('BLOCK_PERSONIL', $perjalananDinas->personil->count(), true, true);
                    foreach ($perjalananDinas->personil as $index => $p) {
                        $templateProcessor->setValue('NO_PERSONIL#' . ($index + 1), ($index + 1)); // Nomor saja, titik bisa ditambahkan di template
                        $templateProcessor->setValue('NAMA_PERSONIL#' . ($index + 1), htmlspecialchars($p->nama ?? '-'));
                        $templateProcessor->setValue('NIP_PERSONIL#' . ($index + 1), htmlspecialchars($p->nip ?? '-'));
                        $templateProcessor->setValue('PANGKAT_GOL_PERSONIL#' . ($index + 1), htmlspecialchars($p->gol ?? '-'));
                        $templateProcessor->setValue('JABATAN_PERSONIL#' . ($index + 1), htmlspecialchars($p->jabatan ?? '-'));
                    }
                } else {
                    $templateProcessor->cloneBlock('BLOCK_PERSONIL', 0, true, true);
                }

                // --- PENYESUAIAN BAGIAN "UNTUK" ---
                $tempatTujuanLengkapUntukWord = htmlspecialchars($perjalananDinas->tujuan_spt ?? '-'); // Ini adalah TUJUANLOKASITUTUANLOKASI
                if ($perjalananDinas->kota_tujuan_id) {
                    // Di gambar: {KOTA_TUJUAN}, Provinsi... -> jadi ada koma sebelum Provinsi
                    $tempatTujuanLengkapUntukWord .= htmlspecialchars($perjalananDinas->kota_tujuan_id);
                }
                if ($perjalananDinas->provinsi_tujuan_id) {
                    // Tambahkan koma hanya jika ada kota sebelumnya ATAU jika tujuan utama tidak kosong
                    if (!empty($perjalananDinas->kota_tujuan_id) || !empty($perjalananDinas->tujuan_spt)) {
                        $tempatTujuanLengkapUntukWord .= ', ';
                    }
                    $tempatTujuanLengkapUntukWord .= 'Provinsi ' . htmlspecialchars($perjalananDinas->provinsi_tujuan_id);
                }

                $templateProcessor->setValue('URAIAN_KEGIATAN', htmlspecialchars($perjalananDinas->uraian_spt ?? 'Melaksanakan Perjalanan Dinas')); // Ini "asdsd" Anda
                $templateProcessor->setValue('TEMPAT_TUJUAN_LENGKAP', $tempatTujuanLengkapUntukWord);
                $templateProcessor->setValue('LAMA_HARI_ANGKA', htmlspecialchars($perjalananDinas->lama_hari ?? '0'));
                $templateProcessor->setValue('LAMA_HARI_TERBILANG', htmlspecialchars(TerbilangHelper::terbilang($perjalananDinas->lama_hari ?? 0)));
                $templateProcessor->setValue('TANGGAL_MULAI_FORMAT', htmlspecialchars($perjalananDinas->tanggal_mulai ? Carbon::parse($perjalananDinas->tanggal_mulai)->translatedFormat('d F Y') : '-')); // Format "22 May 2025"
                $templateProcessor->setValue('TANGGAL_SELESAI_FORMAT', htmlspecialchars($perjalananDinas->tanggal_selesai ? Carbon::parse($perjalananDinas->tanggal_selesai)->translatedFormat('d F Y') : '-')); // Format "31 May 2025"
                // --- AKHIR PENYESUAIAN BAGIAN "UNTUK" ---

                // TTD Kepala Dinas (sesuaikan placeholder dengan gambar)
                $templateProcessor->setValue('JABATAN_PENGIRIM', 'KEPALA DINAS KOMUNIKASI DAN<w:br/>INFORMATIKA KABUPATEN SIAK'); // ${jabatan_pengirim}
                $templateProcessor->setValue('NAMA_PENGIRIM', htmlspecialchars($namaKadis)); // ${nama_pengirim}
                $templateProcessor->setValue('PANGKAT_PENGIRIM', htmlspecialchars($pangkatKadis)); // Ini ada di bawah nama di gambar
                $templateProcessor->setValue('NIP_PENGIRIM', htmlspecialchars($nipKadis)); // ${nip_pengirim}

                // Jika Anda memiliki placeholder terpisah untuk pangkat di bawah nama, tambahkan:
                // $templateProcessor->setValue('PANGKAT_DI_BAWAH_NAMA', htmlspecialchars($pangkatKadis));


                $fileName = $fileNameBase . '.docx';
                $tempFile = tempnam(sys_get_temp_dir(), Str::random(10) . '_spt_');
                $templateProcessor->saveAs($tempFile);

                return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
            } catch (\Exception $e) {
                Log::error("Error generating SPT DOCX (Template): " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
                return redirect()->back()->with('error', 'Gagal membuat file Word SPT. Silakan cek log.');
            }
        }

        return redirect()->back()->with('error', 'Format dokumen tidak valid.');
    }

    public function downloadSPPD(Request $request, PerjalananDinas $perjalananDinas, $format = 'pdf')
    {
        $user = Auth::user();
        $isPersonilDalamPerjalanan = $perjalananDinas->personil()->where('users.id', $user->id)->exists();
        if (!$isPersonilDalamPerjalanan && !$user->hasAnyRole(['operator', 'superadmin', 'atasan', 'verifikator'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses dokumen SPPD ini.');
        }
        if (!in_array($perjalananDinas->status, ['disetujui', 'selesai'])) {
            return redirect()->back()->with('error', 'SPPD belum bisa diunduh karena status perjalanan dinas belum disetujui/selesai.');
        }
        if (empty($perjalananDinas->nomor_spt)) { // Perlu nomor SPT untuk SPPD
            return redirect()->back()->with('error', 'Nomor SPT belum diterbitkan, SPPD tidak bisa digenerate.');
        }


        $namaKadis = config('constants.kadis.nama', 'ROMY LESMANA DERMAWAN, AP, M.Si');
        $pangkatKadis = config('constants.kadis.pangkat', 'Pembina Utama Muda (IV/c)');
        $nipKadis = config('constants.kadis.nip', '197402021993031004');
        $kodeRekening = '2.16.01.2.06.09'; // Default
        preg_match('/kode rekening ([\d\.]+)/i', $perjalananDinas->dasar_spt, $matches);
        if (isset($matches[1])) {
            $kodeRekening = $matches[1];
        }

        $dataForPdf = [
            'perjalananDinas' => $perjalananDinas->load('personil'),
            'namaKadis' => $namaKadis,
            'pangkatKadis' => $pangkatKadis,
            'nipKadis' => $nipKadis,
            'kodeRekening' => $kodeRekening,
            'terbilangHelper' => new TerbilangHelper(),
        ];
        $nomorSPTClean = Str::slug($perjalananDinas->nomor_spt, '-');

        if (strtolower($format) === 'pdf') {
            try {
                Pdf::setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'serif']);
                $pdf = Pdf::loadView('pdf.sppd', $dataForPdf); // Template sppd.blade.php akan loop personil
                return $pdf->download('SPPD-Kolektif-' . $nomorSPTClean . '.pdf');
            } catch (\Exception $e) {
                Log::error("Error generating SPPD PDF: " . $e->getMessage() . " File: " . $e->getFile() . " Line: " . $e->getLine());
                return redirect()->back()->with('error', 'Gagal membuat file PDF SPPD. Silakan cek log.');
            }
        } elseif (strtolower($format) === 'word') {
            $personilUntukSPPD = null;
            $personilIdDiminta = $request->query('personil_id');

            if ($personilIdDiminta) {
                $personilUntukSPPD = $perjalananDinas->personil()->find($personilIdDiminta);
                if (!$personilUntukSPPD) {
                    return redirect()->back()->with('error', 'Personil yang diminta untuk SPPD Word tidak valid untuk perjalanan dinas ini.');
                }
            } elseif ($isPersonilDalamPerjalanan && !$user->hasAnyRole(['operator', 'superadmin', 'atasan', 'verifikator'])) {
                $personilUntukSPPD = $user;
            } else {
                // Jika admin dan tidak ada personil_id, bisa pilih default atau error
                if ($perjalananDinas->personil->isEmpty()) {
                    return redirect()->back()->with('error', 'Tidak ada personil yang ditugaskan untuk SPPD ini.');
                }
                $personilUntukSPPD = $perjalananDinas->personil->first();
            }


            try {
                $templatePath = storage_path('app/templates/template_sppd.docx');
                if (!Storage::disk('local')->exists('templates/template_sppd.docx')) {
                    Log::error('Template SPPD Word tidak ditemukan di: ' . $templatePath);
                    return redirect()->back()->with('error', 'Template dokumen Word SPPD tidak ditemukan.');
                }
                $templateProcessor = new TemplateProcessor($templatePath);

                // --- Mengisi Placeholder SPPD Sesuai Gambar Baru ---

                // Poin 1 (Pejabat yang memberi perintah) - Biasanya teks statis di template
                // $templateProcessor->setValue('PEJABAT_PERINTAH', 'KEPALA DINAS KOMUNIKASI DAN INFORMATIKA');

                // Poin 2
                $templateProcessor->setValue('NAMA_PEGAWAI_SPPD', htmlspecialchars($personilUntukSPPD->nama ?? 'Pegawai Contoh'));

                // Poin 3 (Pangkat, Jabatan, Tingkat Biaya - Digabung)
                $poin3_a = "a. " . htmlspecialchars($personilUntukSPPD->gol ?? 'III/a'); // "AIII" di gambar, sesuaikan format jika perlu
                $poin3_b = "b. " . htmlspecialchars($personilUntukSPPD->jabatan ?? 'Staf Pelaksana');
                $poin3_c = "c. " . htmlspecialchars($personilUntukSPPD->tingkat_biaya_sppd ?? '-'); // Anda perlu field/logika untuk ini
                $templateProcessor->setValue('POIN_3_FULL_SPPD', $poin3_a . '<w:br/>' . $poin3_b . '<w:br/>' . $poin3_c);

                // Poin 4
                $templateProcessor->setValue('MAKSUD_PERJALANAN_SPPD', htmlspecialchars($perjalananDinas->uraian_spt ?? 'asdd'));

                // Poin 5
                $templateProcessor->setValue('ALAT_ANGKUT_SPPD', htmlspecialchars($perjalananDinas->alat_angkut ?? 'Pesawat Udara'));

                // Poin 6 (Tempat Berangkat dan Tujuan - Digabung)
                $poin6_a = "a. " . htmlspecialchars($perjalananDinas->tempat_berangkat_sppd ?? 'Siak Sri Indrapura'); // Asumsi ada field ini atau default
                $tempatTujuanLengkapSPPD = htmlspecialchars($perjalananDinas->tujuan_spt ?? 'Di Hotel Aryaduta Medan...');
                if ($perjalananDinas->kota_tujuan_id && $perjalananDinas->kota_tujuan_id !== ($perjalananDinas->tujuan_spt ?? '')) {
                    $tempatTujuanLengkapSPPD .= ($perjalananDinas->tujuan_spt ? ', ' : '') . htmlspecialchars($perjalananDinas->kota_tujuan_id);
                }
                if ($perjalananDinas->provinsi_tujuan_id) {
                    if (!empty($tempatTujuanLengkapSPPD)) {
                        $tempatTujuanLengkapSPPD .= ', ';
                    }
                    $tempatTujuanLengkapSPPD .= 'Provinsi ' . htmlspecialchars($perjalananDinas->provinsi_tujuan_id);
                }
                $poin6_b = "b. " . $tempatTujuanLengkapSPPD;
                $templateProcessor->setValue('POIN_6_FULL_SPPD', $poin6_a . '<w:br/>' . $poin6_b);

                // Poin 7 (Lama, Tgl Berangkat, Tgl Kembali - Digabung)
                $lamaHariAngka = htmlspecialchars($perjalananDinas->lama_hari ?? '8');
                $lamaHariTerbilang = htmlspecialchars(TerbilangHelper::terbilang($perjalananDinas->lama_hari ?? 8));
                $poin7_a = "a. " . $lamaHariAngka . ' (' . $lamaHariTerbilang . ') hari';
                $poin7_b = "b. " . htmlspecialchars($perjalananDinas->tanggal_mulai ? Carbon::parse($perjalananDinas->tanggal_mulai)->format('d F Y') : '24 May 2025');
                $poin7_c = "c. " . htmlspecialchars($perjalananDinas->tanggal_selesai ? Carbon::parse($perjalananDinas->tanggal_selesai)->format('d F Y') : '31 May 2025');
                $templateProcessor->setValue('POIN_7_FULL_SPPD', $poin7_a . '<w:br/>' . $poin7_b . '<w:br/>' . $poin7_c);

                // Poin 8
                $pengikutList = [];
                if ($perjalananDinas->personil->count() > 1) {
                    foreach ($perjalananDinas->personil as $p) {
                        if ($p->id != $personilUntukSPPD->id) {
                            $pengikutList[] = htmlspecialchars($p->nama);
                        }
                    }
                }
                $templateProcessor->setValue('PENGIKUT_SPPD_LIST', !empty($pengikutList) ? implode('<w:br/>', $pengikutList) : '- Nihil -'); // Di gambar "Kepala Dinas Contoh"

                // Poin 9 (Pembebanan Anggaran - Digabung)
                $poin9_a = "a. Dinas Komunikasi dan Informatika Kabupaten Siak"; // Sesuai gambar
                $poin9_b = "b. " . htmlspecialchars($kodeRekening); // $kodeRekening sudah diambil sebelumnya
                $templateProcessor->setValue('POIN_9_FULL_SPPD', $poin9_a . '<w:br/>' . $poin9_b);

                // Poin 10
                $templateProcessor->setValue('KETERANGAN_LAIN_SPPD', htmlspecialchars($perjalananDinas->keterangan_lain_sppd ?? '-'));

                // TTD dan Bagian Belakang (Pastikan placeholder di template Word Anda sesuai)
                $templateProcessor->setValue('TANGGAL_DIKELUARKAN_SPPD', htmlspecialchars($perjalananDinas->tanggal_spt ? Carbon::parse($perjalananDinas->tanggal_spt)->translatedFormat('d F Y') : '-'));
                $templateProcessor->setValue('NAMA_KADIS_SPPD', htmlspecialchars($namaKadis));
                $templateProcessor->setValue('PANGKAT_KADIS_SPPD', htmlspecialchars($pangkatKadis)); // Jika ada placeholder terpisah
                $templateProcessor->setValue('NIP_KADIS_SPPD', htmlspecialchars($nipKadis));

                // Halaman Belakang (sesuaikan nama placeholder jika berbeda)
                $templateProcessor->setValue('SPPD_NO_BELAKANG', htmlspecialchars($perjalananDinas->nomor_spt . '/SPPD/' . $personilUntukSPPD->id));
                // ... (placeholder halaman belakang lainnya seperti NAMA_KADIS_BELAKANG, dll.)
                // ... (Isi placeholder untuk bagian tiba/berangkat II, III, IV dengan titik-titik atau strip)
                for ($i_sppd_back = 1; $i_sppd_back <= 3; $i_sppd_back++) {
                    $romawi = ($i_sppd_back == 1 ? 'II' : ($i_sppd_back == 2 ? 'III' : 'IV'));
                    $templateProcessor->setValue("TIBA_DI_TUJUAN_{$romawi}", '................................');
                    $templateProcessor->setValue("TGL_TIBA_TUJUAN_{$romawi}", '....................');
                    // ... dst untuk placeholder kosong lainnya ...
                }
                $templateProcessor->setValue('CATATAN_LAIN_LAIN_BELAKANG', '');


                $fileName = 'SPPD-' . Str::slug($perjalananDinas->nomor_spt) . '-' . Str::slug($personilUntukSPPD->nama) . '.docx';
                $tempFile = tempnam(sys_get_temp_dir(), Str::random(10) . '_sppd_');
                $templateProcessor->saveAs($tempFile);

                return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
            } catch (\Exception $e) {
                Log::error("Error generating SPPD DOCX (Template): " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
                return redirect()->back()->with('error', 'Gagal membuat file Word SPPD. Silakan cek log.');
            }
        }
        return redirect()->back()->with('error', 'Format dokumen tidak valid.');
    }

    public function laporanSPT(Request $request) // Menggunakan DataTable Class Builder
    {
        // Jika Anda tidak menggunakan DataTable Class Builder, Anda bisa langsung render view:
        // return view('perjalanan_dinas.laporan.spt_index');

        // Jika menggunakan DataTable Class Builder (misalnya PerjalananDinasDataTable)
        // Anda perlu membuat class ini dengan `php artisan datatable:make PerjalananDinas`
        // dan mengkonfigurasinya. Untuk server-side AJAX di view, cara di bawah lebih umum.
        // Contoh jika menggunakan DataTable class:
        // if (class_exists(PerjalananDinasDataTable::class)) {
        //     $dataTable = app(PerjalananDinasDataTable::class);
        //     return $dataTable->render('perjalanan_dinas.laporan.spt_index');
        // }
        // Jika tidak, fallback ke cara standar
        return view('perjalanan_dinas.laporan.spt_index');
    }

    /**
     * Menyediakan data untuk DataTables pada halaman laporan SPT.
     */
    public function dataTableSPT(Request $request)
    {
        // Ambil data Perjalanan Dinas yang sudah ada Nomor SPT dan statusnya relevan
        $query = PerjalananDinas::with(['personil', 'operator'])
            ->whereNotNull('nomor_spt') // Pastikan nomor SPT sudah ada
            ->where('nomor_spt', '!=', '') // Pastikan nomor SPT tidak kosong
            ->whereIn('status', ['disetujui', 'selesai']) // Hanya yang sudah final
            ->select('perjalanan_dinas.*'); // Pilih semua kolom dari perjalanan_dinas

        // Otorisasi: Superadmin, operator, atasan, verifikator, kadis bisa lihat semua.
        // Pegawai biasa mungkin hanya yang melibatkan dirinya (jika ini halaman umum, bukan personal)
        // Untuk laporan umum, biasanya semua ditampilkan jika user punya hak akses.
        $user = Auth::user();
        if ($user->hasRole('pegawai') && !$user->hasAnyRole(['operator', 'superadmin', 'atasan', 'verifikator', 'kepala dinas'])) {
            $query->whereHas('personil', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }


        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('tanggal_spt', function ($row) {
                return $row->tanggal_spt ? Carbon::parse($row->tanggal_spt)->translatedFormat('d M Y') : '-';
            })
            ->editColumn('jenis_spt', function ($row) {
                return ucfirst(str_replace('_', ' ', $row->jenis_spt));
            })
            ->addColumn('kota_tujuan_display', function ($row) {
                $tujuan = [];
                if ($row->tujuan_spt) $tujuan[] = $row->tujuan_spt; // Tempat/Instansi Tujuan Utama
                if ($row->kota_tujuan_id) $tujuan[] = $row->kota_tujuan_id;
                // if ($row->provinsi_tujuan_id) $tujuan[] = "Prov. " . $row->provinsi_tujuan_id; // Bisa ditambahkan jika perlu
                return implode(', ', $tujuan);
            })
            ->editColumn('dasar_spt', function ($row) {
                return Str::limit(strip_tags(str_replace(["\r\n", "\r", "\n"], ' ', $row->dasar_spt)), 50);
            })
            ->editColumn('uraian_spt', function ($row) {
                return Str::limit(strip_tags(str_replace(["\r\n", "\r", "\n"], ' ', $row->uraian_spt)), 50);
            })
            ->addColumn('jumlah_personil', function ($row) {
                return $row->personil->count();
            })
            ->addColumn('nama_personil', function ($row) {
                return Str::limit($row->personil->pluck('nama')->implode(', '), 50);
            })
            ->editColumn('lama_hari', function ($row) {
                return $row->lama_hari . ' hari';
            })
            ->addColumn('tanggal_mulai_spt', function ($row) {
                return $row->tanggal_mulai ? Carbon::parse($row->tanggal_mulai)->translatedFormat('d M Y') : '-';
            })
            ->addColumn('tanggal_selesai_spt', function ($row) {
                return $row->tanggal_selesai ? Carbon::parse($row->tanggal_selesai)->translatedFormat('d M Y') : '-';
            })
            // Anda bisa menambahkan kolom aksi jika diperlukan (misal, link ke detail atau download)
            // ->addColumn('action', function($row){
            //      $btn = '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">View</a>';
            //      return $btn;
            // })
            // ->rawColumns(['action'])
            ->make(true);
    }
}
