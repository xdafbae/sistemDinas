<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>SPPD - {{ $perjalananDinas->nomor_spt ?? 'Nomor Belum Ada' }}</title>
    <style>
        @page { margin: 20px 25px; }
        body { font-family: 'Arial', sans-serif; font-size: 10px; }
        .kop-surat { text-align: center; margin-bottom: 10px; }
        .kop-surat p.pemkab { font-size: 12px; font-weight: bold; margin:0; }
        .kop-surat h2.dinas { font-size: 14px; font-weight: bold; text-transform: uppercase; margin:0; }
        .kop-surat p.alamat-kop { font-size: 8px; margin:0; line-height: 1.2; }
        .garis-kop-sppd { border: 0; border-top: 2.5px solid black; margin-top: 2px; margin-bottom: 0.5px; }
        .garis-kop-tipis-sppd { border: 0; border-top: 0.5px solid black; margin-top: 0.5px; margin-bottom: 8px; }

        .judul-sppd h4 { text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 2px; font-size: 11px; }
        .judul-sppd p { text-align: center; margin-top:0; margin-bottom: 10px; font-size: 10px;}

        table.sppd-main { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 10px;}
        table.sppd-main th, table.sppd-main td { border: 1px solid black; padding: 3px; vertical-align: top; line-height: 1.3; }
        table.sppd-main td:nth-child(1) { width: 3%; text-align: center; }
        table.sppd-main td:nth-child(2) { width: 37%; }
        table.sppd-main td:nth-child(3) { width: 60%; }

        table.sppd-ttd { width: 100%; margin-top: 15px; font-size: 10px;}
        table.sppd-ttd td { width: 50%; vertical-align: top; text-align:center; }
        .ttd-placeholder { height: 50px; }

        .sppd-belakang { margin-top: 10px; }
        .sppd-belakang table { width: 100%; border-collapse: collapse; font-size: 9px; }
        .sppd-belakang th, .sppd-belakang td { border: 1px solid black; padding: 2px; text-align: left; line-height: 1.2;}
        .sppd-belakang .header-belakang { font-weight: bold; text-align:center; background-color: #f2f2f2; }
        .sppd-belakang .ttd-placeholder-belakang { height: 40px; }
        .catatan-penting { font-size: 7px; margin-top: 10px; text-align: justify;}
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @if(isset($perjalananDinas) && $perjalananDinas->personil->count() > 0)
        @foreach($perjalananDinas->personil as $personil_sppd)
            {{-- Halaman 1 SPPD (Informasi Utama) --}}
            <div class="kop-surat">
                <p class="pemkab">PEMERINTAH KABUPATEN SIAK</p>
                <h2 class="dinas">DINAS KOMUNIKASI DAN INFORMATIKA</h2>
                <p class="alamat-kop">Komplek Perkantoran Tanjung Agung Kecamatan Mempura Kabupaten Siak Provinsi Riau<br>
                e-mail: diskominfo@mail.siakkab.go.id</p>
            </div>
            <hr class="garis-kop-sppd">
            <hr class="garis-kop-tipis-sppd">

            <div class="judul-sppd">
                <h4>SURAT PERINTAH PERJALANAN DINAS</h4>
                <p>(SPPD)</p>
            </div>

            <table class="sppd-main">
                <tr>
                    <td>1.</td>
                    <td>Pejabat yang memberi perintah</td>
                    <td>KEPALA DINAS KOMUNIKASI DAN INFORMATIKA</td>
                </tr>
                <tr>
                    <td>2.</td>
                    <td>Nama pegawai yang diperintah</td>
                    <td><strong>{{ $personil_sppd->nama ?? '-' }}</strong></td>
                </tr>
                <tr>
                    <td>3.</td>
                    <td>
                        a. Pangkat dan Golongan<br>
                        b. Jabatan<br>
                        c. Tingkat menurut peraturan perjalanan
                    </td>
                    <td>
                        a. {{ $personil_sppd->gol ?? '-' }} <br>
                        b. {{ $personil_sppd->jabatan ?? '-' }} <br>
                        c. - {{-- Tingkat Biaya Perjalanan Dinas (Perlu mapping dari SBU atau aturan lain) --}}
                    </td>
                </tr>
                <tr>
                    <td>4.</td>
                    <td>Maksud Perjalanan Dinas</td>
                    <td>{{ $perjalananDinas->uraian_spt ?? '-' }}</td>
                </tr>
                <tr>
                    <td>5.</td>
                    <td>Alat angkut yang dipergunakan</td>
                    <td>{{ $perjalananDinas->alat_angkut ?? 'Kendaraan Dinas / Umum' }}</td>
                </tr>
                <tr>
                    <td>6.</td>
                    <td>
                        a. Tempat berangkat<br>
                        b. Tempat tujuan
                    </td>
                    <td>
                        a. Siak Sri Indrapura<br>
                        b. {{ $perjalananDinas->tujuan_spt ?? '-' }}
                            @if($perjalananDinas->kota_tujuan_id), {{ $perjalananDinas->kota_tujuan_id }} @endif
                            @if($perjalananDinas->provinsi_tujuan_id), Provinsi {{ $perjalananDinas->provinsi_tujuan_id }} @endif
                    </td>
                </tr>
                <tr>
                    <td>7.</td>
                    <td>
                        a. Lamanya Perjalanan Dinas<br>
                        b. Tanggal berangkat<br>
                        c. Tanggal harus kembali
                    </td>
                    <td>
                        a. {{ $perjalananDinas->lama_hari ?? '0' }} ({{ $terbilangHelper->terbilang($perjalananDinas->lama_hari ?? 0) }}) hari<br>
                        b. {{ $perjalananDinas->tanggal_mulai ? \Carbon\Carbon::parse($perjalananDinas->tanggal_mulai)->translatedFormat('d F Y') : '-' }}<br>
                        c. {{ $perjalananDinas->tanggal_selesai ? \Carbon\Carbon::parse($perjalananDinas->tanggal_selesai)->translatedFormat('d F Y') : '-' }}
                    </td>
                </tr>
                <tr>
                    <td>8.</td>
                    <td>Pengikut</td>
                    <td>
                        @php $pengikutCounterSppd = 0; @endphp
                        @foreach($perjalananDinas->personil as $p_ikut_sppd)
                            @if($p_ikut_sppd->id != $personil_sppd->id)
                                {{ $loop->first && $pengikutCounterSppd == 0 ? '' : '<br>' }}{{ $p_ikut_sppd->nama }}
                                @php $pengikutCounterSppd++; @endphp
                            @endif
                        @endforeach
                        @if($pengikutCounterSppd == 0)
                            - Nihil -
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>9.</td>
                    <td>
                        Pembebanan Anggaran<br>
                        a. Instansi<br>
                        b. Mata Anggaran
                    </td>
                    <td>
                        <br>
                        a. Dinas Komunikasi dan Informatika Kabupaten Siak<br>
                        b. {{ $kodeRekening ?? '-' }}
                    </td>
                </tr>
                <tr>
                    <td>10.</td>
                    <td>Keterangan lain-lain</td>
                    <td>-</td>
                </tr>
            </table>

            <table class="sppd-ttd">
                <tr><td></td>
                    <td>
                        Dikeluarkan di: Siak Sri Indrapura<br>
                        Pada Tanggal : {{ $perjalananDinas->tanggal_spt ? \Carbon\Carbon::parse($perjalananDinas->tanggal_spt)->translatedFormat('d F Y') : '-' }}<br><br>
                        KEPALA DINAS KOMUNIKASI DAN<br>INFORMATIKA KABUPATEN SIAK,
                        <div class="ttd-placeholder"></div>
                        <strong><u>{{ $namaKadis ?? '-' }}</u></strong><br>
                        {{ $pangkatKadis ?? '-' }}<br>
                        NIP. {{ $nipKadis ?? '-' }}
                    </td>
                </tr>
            </table>

            {{-- Halaman Belakang SPPD --}}
            <div class="page-break"></div>
            <div class="sppd-belakang">
                <table style="width:100%; margin-bottom: 15px;">
                    <tr>
                        <td style="width:50%; border:none;"></td>
                        <td style="width:50%; border:none; text-align:left; padding-left:10px;">
                            SPPD No.         : {{ $perjalananDinas->nomor_spt ?? '_____________________' }}/SPPD/{{ $personil_sppd->id }} <br>
                            Berangkat dari : Siak Sri Indrapura <br>
                            (Tempat Kedudukan) <br>
                            Pada Tanggal  : {{ $perjalananDinas->tanggal_mulai ? \Carbon\Carbon::parse($perjalananDinas->tanggal_mulai)->translatedFormat('d F Y') : '_____________________' }} <br>
                            Ke                         : {{ $perjalananDinas->tujuan_spt ?? '-' }}
                            @if($perjalananDinas->kota_tujuan_id), {{ $perjalananDinas->kota_tujuan_id }} @endif
                            <div style="margin-top:15px; text-align:center;">
                                KEPALA DINAS KOMUNIKASI DAN<br>INFORMATIKA KABUPATEN SIAK,
                                <div class="ttd-placeholder"></div>
                                <strong><u>{{ $namaKadis ?? '-' }}</u></strong><br>
                                NIP. {{ $nipKadis ?? '-' }}
                            </div>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr><td style="width: 50%;" class="header-belakang">II. Tiba di</td><td style="width: 50%;" class="header-belakang">Berangkat dari</td></tr>
                    <tr><td>    : {{ $perjalananDinas->tujuan_spt ?? '-' }} @if($perjalananDinas->kota_tujuan_id), {{ $perjalananDinas->kota_tujuan_id }} @endif</td><td>Ke :</td></tr>
                    <tr><td>Pada Tanggal :</td><td>Pada Tanggal :</td></tr>
                    <tr><td>Kepala :</td><td>Kepala :</td></tr>
                    <tr><td class="ttd-placeholder-belakang"></td><td class="ttd-placeholder-belakang"></td></tr>
                    <tr><td>(..............................................)</td><td>(..............................................)</td></tr>
                    <tr><td>NIP.</td><td>NIP.</td></tr>

                    <tr><td class="header-belakang">III. Tiba di</td><td class="header-belakang">Berangkat dari</td></tr>
                    <tr><td>    :</td><td>Ke :</td></tr>
                    <tr><td>Pada Tanggal :</td><td>Pada Tanggal :</td></tr>
                    <tr><td>Kepala :</td><td>Kepala :</td></tr>
                    <tr><td class="ttd-placeholder-belakang"></td><td class="ttd-placeholder-belakang"></td></tr>
                    <tr><td>(..............................................)</td><td>(..............................................)</td></tr>
                    <tr><td>NIP.</td><td>NIP.</td></tr>

                    <tr><td class="header-belakang">IV. Tiba di</td><td class="header-belakang">Berangkat dari</td></tr>
                    <tr><td>    :</td><td>Ke :</td></tr>
                    <tr><td>Pada Tanggal :</td><td>Pada Tanggal :</td></tr>
                    <tr><td>Kepala :</td><td>Kepala :</td></tr>
                    <tr><td class="ttd-placeholder-belakang"></td><td class="ttd-placeholder-belakang"></td></tr>
                    <tr><td>(..............................................)</td><td>(..............................................)</td></tr>
                    <tr><td>NIP.</td><td>NIP.</td></tr>

                    <tr>
                        <td colspan="2" style="padding-top:10px;">
                            V. Tiba kembali di : Siak Sri Indrapura <br>
                                 Pada Tanggal     : {{ $perjalananDinas->tanggal_selesai ? \Carbon\Carbon::parse($perjalananDinas->tanggal_selesai)->addDay()->translatedFormat('d F Y') : '_____________________' }}
                            <div style="margin-top:5px; text-align:center;">
                                Telah diperiksa, dengan keterangan bahwa perjalanan tersebut di atas benar dilakukan atas<br>perintahnya dan semata-mata untuk kepentingan jabatan dalam waktu yang sesingkat-singkatnya.
                                <br><br>
                                KEPALA DINAS KOMUNIKASI DAN<br>INFORMATIKA KABUPATEN SIAK,
                                <div class="ttd-placeholder"></div>
                                <strong><u>{{ $namaKadis ?? '-' }}</u></strong><br>
                                NIP. {{ $nipKadis ?? '-' }}
                            </div>
                        </td>
                    </tr>
                    <tr><td colspan="2" class="header-belakang">VI. CATATAN LAIN-LAIN</td></tr>
                    <tr><td colspan="2" style="height:40px; vertical-align:top;"></td></tr>
                </table>
                <div class="catatan-penting">
                    VII. PERHATIAN: Pejabat yang berwenang menerbitkan SPPD, pegawai yang melakukan perjalanan dinas, para pejabat yang mengesahkan tanggal berangkat/tiba serta Bendaharawan bertanggung jawab berdasarkan peraturan-peraturan Keuangan Negara apabila Negara mendapat rugi akibat kesalahan, kealpaannya.
                </div>
            </div>
            @if (!$loop->last) <div class="page-break"></div> @endif
        @endforeach
    @else
        <p>Data perjalanan dinas atau personil tidak ditemukan.</p>
    @endif
</body>
</html>