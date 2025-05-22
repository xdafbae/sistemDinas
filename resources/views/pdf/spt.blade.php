<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>SPT - {{ $perjalananDinas->nomor_spt ?? 'Nomor Belum Ada' }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12px; margin: 30px; line-height: 1.4; }
        .kop-surat { text-align: center; margin-bottom: 15px; }
        .kop-surat img.logo-kabupaten { float: left; width: 65px; height: auto; margin-top: 5px; margin-right: 10px; }
        .kop-surat img.logo-dinas { float: right; width: 65px; height: auto; margin-top: 5px; margin-left: 10px; }
        .kop-surat .kop-tengah { display: inline-block; text-align: center; }
        .kop-surat h1 { font-size: 15px; font-weight: bold; margin:0; }
        .kop-surat h2 { font-size: 17px; font-weight: bold; text-transform: uppercase; margin:0; }
        .kop-surat p.alamat { font-size: 9px; margin:0; }
        .garis-kop { border: 0; border-top: 2.5px solid black; margin-top: 5px; margin-bottom: 1px; }
        .garis-kop-tipis { border: 0; border-top: 0.5px solid black; margin-top: 0px; margin-bottom: 15px; }

        .judul-spt { text-align: center; margin-bottom: 15px; }
        .judul-spt h4 { margin: 0; font-weight: bold; text-decoration: underline; text-transform: uppercase; font-size: 13px;}
        .judul-spt p { margin: 3px 0 0 0; font-size: 11px;}

        .dasar-hukum { margin-bottom: 10px; }
        .dasar-hukum p { margin:0; font-weight: normal; }
        .dasar-hukum ol { padding-left: 30px; margin-top: 0; margin-bottom: 0; }
        .dasar-hukum ol li { margin-bottom: 2px; text-align: justify; }

        .memerintahkan { text-align: center; font-weight: bold; margin-top: 15px; margin-bottom: 8px; font-size: 12px;}

        .kepada { margin-bottom: 8px; }
        .kepada p { margin:0; font-weight: normal;}
        table.info-personil { width: 100%; margin-bottom: 5px; margin-left: 0px; }
        table.info-personil td { vertical-align: top; padding: 1px 0; font-size: 11px;}
        table.info-personil td.label-personil { width: 120px; padding-left:20px; }
        table.info-personil td.separator-personil { width: 5px; text-align: center; }
        table.info-personil td.nomor-personil { width: 15px; text-align: left; vertical-align: top;}

        .untuk { margin-top:8px; }
        .untuk p { margin:0; font-weight: normal; }
        .isi-tugas { padding-left: 20px; text-align: justify; }
        .isi-tugas ol { padding-left: 20px; margin-top: 0; }
        .isi-tugas ol li { margin-bottom: 2px; }

        .penutup { margin-top: 25px; }
        .ttd-kanan { width: 45%; float: right; text-align: center; margin-top: 15px;}
        .ttd-kanan .jabatan-ttd { margin-bottom: 50px; font-weight: bold; line-height:1.3; }
        .ttd-kanan .nama-ttd { font-weight: bold; text-decoration: underline; }
        .clearfix::after { content: ""; clear: both; display: table; }
        .catatan-tte { font-size: 8px; margin-top: 70px; border-top: 0.5px dashed #999; padding-top: 3px; text-align:left;}
    </style>
</head>
<body>
    <div class="kop-surat clearfix">
        {{-- <img src="{{ public_path('assets/img/logo_kab_siak_fix.png') }}" alt="Logo Kabupaten Siak" class="logo-kabupaten"> --}}
        {{-- <img src="{{ public_path('assets/img/logo_diskominfo_fix.png') }}" alt="Logo Dinas Kominfo" class="logo-dinas"> --}}
        <div class="kop-tengah">
            <p>PEMERINTAH KABUPATEN SIAK</p>
            <h2>DINAS KOMUNIKASI DAN INFORMATIKA</h2>
            <p class="alamat">Komplek Perkantoran Tanjung Agung Kecamatan Mempura Kabupaten Siak Provinsi Riau<br>
            email: diskominfo@mail.siakkab.go.id</p>
        </div>
    </div>
    <hr class="garis-kop">
    <hr class="garis-kop-tipis">

    <div class="judul-spt">
        <h4>SURAT PERINTAH TUGAS</h4>
        <p>Nomor : {{ $perjalananDinas->nomor_spt ?? 'Nomor Belum Ada' }}</p>
    </div>

    <div class="dasar-hukum">
        <p>Dasar :</p>
        <ol type="1">
            @forelse ($dasarSPTList ?? [] as $dasar)
                <li>{!! nl2br(e($dasar)) !!}</li>
            @empty
                <li>-</li>
            @endforelse
        </ol>
    </div>

    <p class="memerintahkan">M E M E R I N T A H K A N :</p>

    <div class="kepada">
        <p>Kepada :</p>
        @foreach($perjalananDinas->personil as $personil_spt) {{-- Menggunakan variabel loop yang berbeda --}}
        <table class="info-personil">
            <tr>
                <td class="nomor-personil">{{ $loop->iteration }}.</td>
                <td class="label-personil">Nama</td>
                <td class="separator-personil">:</td>
                <td><strong>{{ $personil_spt->nama ?? '-' }}</strong></td>
            </tr>
            <tr>
                <td></td>
                <td class="label-personil">NIP</td>
                <td class="separator-personil">:</td>
                <td>{{ $personil_spt->nip ?? '-' }}</td>
            </tr>
            <tr>
                <td></td>
                <td class="label-personil">Pangkat/Golongan</td>
                <td class="separator-personil">:</td>
                <td>{{ $personil_spt->gol ?? '-' }}</td>
            </tr>
            <tr>
                <td></td>
                <td class="label-personil">Jabatan</td>
                <td class="separator-personil">:</td>
                <td>{{ $personil_spt->jabatan ?? '-' }}</td>
            </tr>
        </table>
        @endforeach
    </div>

    <div class="untuk">
        <p>Untuk :</p>
        <div class="isi-tugas">
            <ol type="1">
                <li>{!! nl2br(e($perjalananDinas->uraian_spt ?? 'Melaksanakan Perjalanan Dinas')) !!} ke {{ $perjalananDinas->tujuan_spt ?? '-' }}
                    @if($perjalananDinas->kota_tujuan_id), {{ $perjalananDinas->kota_tujuan_id }} @endif
                    @if($perjalananDinas->provinsi_tujuan_id), Provinsi {{ $perjalananDinas->provinsi_tujuan_id }} @endif
                    selama {{ $perjalananDinas->lama_hari ?? '0' }} ({{ $terbilangHelper->terbilang($perjalananDinas->lama_hari ?? 0) }}) hari terhitung mulai tanggal {{ $perjalananDinas->tanggal_mulai ? \Carbon\Carbon::parse($perjalananDinas->tanggal_mulai)->translatedFormat('d F Y') : '-' }}
                    s.d {{ $perjalananDinas->tanggal_selesai ? \Carbon\Carbon::parse($perjalananDinas->tanggal_selesai)->translatedFormat('d F Y') : '-' }}.
                </li>
                <li>Surat Perintah Tugas ini dilaksanakan dengan seksama dan melaporkan hasilnya.</li>
            </ol>
        </div>
    </div>

    <div class="penutup clearfix">
        <div class="ttd-kanan">
            Ditetapkan di Siak Sri Indrapura<br>
            Pada tanggal {{ $perjalananDinas->tanggal_spt ? \Carbon\Carbon::parse($perjalananDinas->tanggal_spt)->translatedFormat('d F Y') : '-' }}<br>
            <span class="jabatan-ttd">KEPALA DINAS KOMUNIKASI DAN<br>INFORMATIKA KABUPATEN SIAK,</span>
            <br>
            <span class="nama-ttd">{{ $namaKadis ?? '-' }}</span><br>
            <span>{{ $pangkatKadis ?? '-' }}</span><br>
            <span>NIP. {{ $nipKadis ?? '-' }}</span>
        </div>
    </div>
    @if(config('constants.tte_aktif', false)) {{-- Ambil dari config jika ada TTE --}}
    <div class="catatan-tte">
        Catatan:<br>
        1.UU ITE No 11 Tahun 2008 Pasal 5 Ayat 1 "Informasi Elektronik dan/atau Dokumen Elektronik dan/atau hasil cetaknya merupakan alat bukti hukum yang sah."<br>
        2.Peraturan Bupati Siak Nomor 105 Tahun 2023 tentang Penggunaan Tanda Tangan Elektronik.<br>
        3.Dokumen ini telah ditandatangani secara elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik (BSrE), BSSN Scan QR Code menggunakan QR Code reader untuk membuktikan keaslian dokumen.
    </div>
    @endif
</body>
</html>