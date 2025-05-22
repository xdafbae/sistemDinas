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
                ->whereIn('status', ['disetujui', 'selesai']); // Hanya yang sudah disetujui atau selesai

            // Filter agar pegawai hanya melihat perjalanannya, sedangkan role lain melihat semua
            if ($user->hasRole('pegawai') && !$user->hasAnyRole(['operator', 'superadmin', 'atasan', 'verifikator'])) {
                $query->whereHas('personil', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('nomor_spt_display', fn($pd) => $pd->nomor_spt)
                ->addColumn('tanggal_spt_formatted', fn($pd) => Carbon::parse($pd->tanggal_spt)->translatedFormat('d M Y'))
                ->addColumn('tujuan_spt_display', fn($pd) => $pd->tujuan_spt)
                ->addColumn('personil_list', fn($pd) => $pd->personil->pluck('nama')->implode(', '))
                ->addColumn('tanggal_pelaksanaan', fn($pd) => Carbon::parse($pd->tanggal_mulai)->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($pd->tanggal_selesai)->translatedFormat('d M Y'))
                ->editColumn('status', function ($perjalanan) {
                    $statusText = str_replace('_', ' ', $perjalanan->status);
                    $statusText = ucwords($statusText);
                    $badgeClass = 'bg-gradient-secondary'; // Default
                    if ($perjalanan->status === 'disetujui') $badgeClass = 'bg-gradient-success';
                    if ($perjalanan->status === 'selesai') $badgeClass = 'bg-gradient-primary';
                    return "<span class='badge {$badgeClass}'>{$statusText}</span>";
                })
                ->addColumn('action', function ($perjalanan) {
                    if (!in_array($perjalanan->status, ['disetujui', 'selesai'])) {
                        return '<span class="badge bg-gradient-warning">Menunggu Penyelesaian</span>';
                    }
                    $sptPdfUrl = route('dokumen.spt.download', ['perjalananDinas' => $perjalanan->id, 'format' => 'pdf']);
                    $sptWordUrl = route('dokumen.spt.download', ['perjalananDinas' => $perjalanan->id, 'format' => 'word']);
                    $sppdPdfUrl = route('dokumen.sppd.download', ['perjalananDinas' => $perjalanan->id, 'format' => 'pdf']);

                    // Untuk SPPD Word, kita bisa buat link per personil jika diperlukan
                    // atau link umum yang akan men-download untuk personil pertama/yang login
                    $sppdWordUrl = route('dokumen.sppd.download', ['perjalananDinas' => $perjalanan->id, 'format' => 'word']);
                    // Jika ingin link per personil:
                    // $sppdWordButtons = '';
                    // foreach($perjalanan->personil as $p) {
                    //     $url = route('dokumen.sppd.download', ['perjalananDinas' => $perjalanan->id, 'format' => 'word', 'personil_id' => $p->id]);
                    //     $sppdWordButtons .= '<a href="'.$url.'" class="btn btn-warning btn-sm me-1" target="_blank" data-bs-toggle="tooltip" title="SPPD Word '.htmlspecialchars($p->nama).'"><i class="fas fa-file-word"></i></a>';
                    // }

                    return '
                        <div class="btn-group btn-group-sm mb-1" role="group" aria-label="SPT Actions">
                            <a href="' . $sptPdfUrl . '" class="btn btn-danger" target="_blank" data-bs-toggle="tooltip" title="Download SPT (PDF)"><i class="fas fa-file-pdf"></i> SPT PDF</a>
                            <a href="' . $sptWordUrl . '" class="btn btn-primary" target="_blank" data-bs-toggle="tooltip" title="Download SPT (Word)"><i class="fas fa-file-word"></i> SPT DOCX</a>
                        </div>
                        <div class="btn-group btn-group-sm" role="group" aria-label="SPPD Actions">
                            <a href="' . $sppdPdfUrl . '" class="btn btn-danger" target="_blank" data-bs-toggle="tooltip" title="Download SPPD (PDF)"><i class="fas fa-file-pdf"></i> SPPD PDF</a>
                            <a href="' . $sppdWordUrl . '" class="btn btn-warning" target="_blank" data-bs-toggle="tooltip" title="Download SPPD (Word)"><i class="fas fa-file-word"></i> SPPD DOCX</a>
        
                        </div>
                    ';
                })
                ->rawColumns(['action', 'status'])
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
        // ... (Otorisasi, data Kadis, kodeRekening sama) ...
        $user = Auth::user();
        $isPersonilDalamPerjalanan = $perjalananDinas->personil()->where('users.id', $user->id)->exists();
        if (!$isPersonilDalamPerjalanan && !$user->hasAnyRole(['operator', 'superadmin', 'atasan', 'verifikator'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses dokumen SPPD ini.');
        }
        if (!in_array($perjalananDinas->status, ['disetujui', 'selesai'])) {
            return redirect()->back()->with('error', 'SPPD belum bisa diunduh karena status perjalanan dinas belum disetujui/selesai.');
        }
        $namaKadis = config('constants.kadis.nama', 'ROMY LESMANA DERMAWAN, AP, M.Si');
        $pangkatKadis = config('constants.kadis.pangkat', 'Pembina Utama Muda (IV/c)');
        $nipKadis = config('constants.kadis.nip', '197402021993031004');
        $kodeRekening = '2.16.01.2.06.09';
        preg_match('/kode rekening ([\d\.]+)/i', $perjalananDinas->dasar_spt, $matches);
        if (isset($matches[1])) {
            $kodeRekening = $matches[1];
        }

        $dataForPdf = [ /* ... (sama) ... */
            'perjalananDinas' => $perjalananDinas->load('personil'),
            'namaKadis' => $namaKadis,
            'pangkatKadis' => $pangkatKadis,
            'nipKadis' => $nipKadis,
            'kodeRekening' => $kodeRekening,
            'terbilangHelper' => new TerbilangHelper(),
        ];
        $nomorSPTClean = Str::slug($perjalananDinas->nomor_spt, '-');

        if (strtolower($format) === 'pdf') {
            // ... (Logika PDF sama) ...
            try {
                Pdf::setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'serif']);
                $pdf = Pdf::loadView('pdf.sppd', $dataForPdf);
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
            } elseif ($isPersonilDalamPerjalanan && !$user->hasAnyRole(['operator', 'superadmin', 'atasan', 'verifikator'])) {
                $personilUntukSPPD = $user;
            } else {
                $personilUntukSPPD = $perjalananDinas->personil()->first();
            }
            if (!$personilUntukSPPD) {
                return redirect()->back()->with('error', 'Personil untuk SPPD Word tidak dapat ditentukan atau tidak valid.');
            }

            try {
                $templatePath = storage_path('app/templates/template_sppd.docx');
                // ... (cek file exists) ...
                $templateProcessor = new TemplateProcessor($templatePath);

                // Mengisi placeholder SPPD
                $templateProcessor->setValue('NAMA_PEGAWAI_SPPD', htmlspecialchars($personilUntukSPPD->nama ?? 'Pegawai Contoh'));

                // --- POIN 3 ---
                $poin3_a = "a. " . htmlspecialchars($personilUntukSPPD->gol ?? 'III/a');
                $poin3_b = "b. " . htmlspecialchars($personilUntukSPPD->jabatan ?? 'Staf Pelaksana');
                $poin3_c = "c. " . htmlspecialchars($personilUntukSPPD->tingkat_biaya ?? '-'); // Anda perlu field 'tingkat_biaya' atau logika mapping
                $templateProcessor->setValue('POIN_3_FULL_SPPD', $poin3_a . '<w:br/>' . $poin3_b . '<w:br/>' . $poin3_c);

                // Poin 4
                $templateProcessor->setValue('MAKSUD_PERJALANAN_SPPD', htmlspecialchars($perjalananDinas->uraian_spt ?? 'asdd'));

                // Poin 5
                $templateProcessor->setValue('ALAT_ANGKUT_SPPD', htmlspecialchars($perjalananDinas->alat_angkut ?? 'Pesawat Udara'));

                // --- POIN 6 ---
                $poin6_a = "a. Siak Sri Indrapura"; // Sesuai gambar
                $tempatTujuanLengkapSPPD = htmlspecialchars($perjalananDinas->tujuan_spt ?? 'Di Hotel Aryaduta Medan Jl. Kapten Maulana Lubis No.8 Petisah Tengah, Kota Medan');
                if ($perjalananDinas->kota_tujuan_id && $perjalananDinas->kota_tujuan_id !== ($perjalananDinas->tujuan_spt ?? '')) { // Hindari duplikasi jika tujuan_spt sudah kota
                    $tempatTujuanLengkapSPPD .= ($perjalananDinas->tujuan_spt ? ', ' : '') . htmlspecialchars($perjalananDinas->kota_tujuan_id);
                }
                if ($perjalananDinas->provinsi_tujuan_id) {
                    if (!empty($tempatTujuanLengkapSPPD)) { // Tambahkan koma jika ada teks sebelumnya
                        $tempatTujuanLengkapSPPD .= ', ';
                    }
                    $tempatTujuanLengkapSPPD .= 'Provinsi ' . htmlspecialchars($perjalananDinas->provinsi_tujuan_id);
                }
                $poin6_b = "b. " . $tempatTujuanLengkapSPPD;
                $templateProcessor->setValue('POIN_6_FULL_SPPD', $poin6_a . '<w:br/>' . $poin6_b);


                // --- POIN 7 ---
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
                $templateProcessor->setValue('PENGIKUT_SPPD_LIST', !empty($pengikutList) ? implode('<w:br/>', $pengikutList) : '- Nihil -');


                // --- POIN 9 ---
                $poin9_a = "a. Dinas Komunikasi dan Informatika Kabupaten Siak"; // Sesuai gambar
                $poin9_b = "b. " . htmlspecialchars($kodeRekening); // $kodeRekening sudah diambil sebelumnya
                $templateProcessor->setValue('POIN_9_FULL_SPPD', $poin9_a . '<w:br/>' . $poin9_b);


                // Poin 10
                $templateProcessor->setValue('KETERANGAN_LAIN_SPPD', htmlspecialchars($perjalananDinas->keterangan_lain_sppd ?? '-'));


                // TTD dan Bagian Belakang (Pastikan placeholder di template Word Anda sesuai)
                $templateProcessor->setValue('TANGGAL_DIKELUARKAN_SPPD', htmlspecialchars($perjalananDinas->tanggal_spt ? Carbon::parse($perjalananDinas->tanggal_spt)->translatedFormat('d F Y') : '-'));
                $templateProcessor->setValue('NAMA_KADIS_SPPD', htmlspecialchars($namaKadis));
                // $templateProcessor->setValue('PANGKAT_KADIS_SPPD', htmlspecialchars($pangkatKadis)); // Jika ada placeholder terpisah
                $templateProcessor->setValue('NIP_KADIS_SPPD', htmlspecialchars($nipKadis));

                // Halaman Belakang (sesuaikan placeholder)
                $templateProcessor->setValue('SPPD_NO_BELAKANG', htmlspecialchars($perjalananDinas->nomor_spt . '/SPPD/' . $personilUntukSPPD->id));
                // ... (placeholder halaman belakang lainnya) ...


                $fileName = 'SPPD-' . Str::slug($perjalananDinas->nomor_spt) . '-' . Str::slug($personilUntukSPPD->nama) . '.docx';
                $tempFile = tempnam(sys_get_temp_dir(), Str::random(10) . '_sppd_');
                $templateProcessor->saveAs($tempFile);

                return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
            } catch (\Exception $e) {
                Log::error("Error generating SPPD DOCX (Template): " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
                return redirect()->back()->with('error', 'Gagal membuat file Word SPPD. Silakan cek log.');
            }
        }
    }
}
