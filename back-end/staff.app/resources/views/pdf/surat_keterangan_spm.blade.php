<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        /* ===== PAGE ===== */
        @page {
            size: A4;
            margin: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
            margin: 0;
        }

        /* ===== KOP SURAT ===== */
        .kop {
            margin-top: -0.1cm;
            margin-left: -1cm;
            margin-right: -1cm;
        }

        .kop img {
            width: 100%;
            max-height: 155px;
            object-fit: contain;
            display: block;
        }

        /* ===== KONTEN ===== */
        .content {
            padding-right: 2cm;
            padding-bottom: 1cm;
            padding-left: 2cm;
        }

        .text-center {
            text-align: center;
        }

        .text-justify {
            text-align: justify;
        }

        .text-bold {
            font-weight: bold;
        }

        .text-underline {
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
            padding: 4px 0;
        }

        .margin-bottom-10 {
            margin-bottom: 10px;
        }

        .margin-bottom-20 {
            margin-bottom: 15px;
        }

        /* Styling Identitas & Tugas */
        .table-data {
            margin-left: 15px;
            margin-bottom: 15px;
        }

        .table-data td {
            padding: 2px 0;
        }
    </style>
</head>

<body>

    <!-- KOP SURAT -->
    <div class="kop">
        @if(isset($kopBase64))
        <img src="{{ $kopBase64 }}" alt="Kop Surat">
        @endif
    </div>

    <!-- ISI SURAT -->
    <div class="content">

        <div class="text-center text-bold text-underline" style="font-size: 14pt; margin-top: 10px;">
            SURAT KETERANGAN SPM
        </div>
        <div class="text-center margin-bottom-20" style="font-size: 11pt;">
            Nomor: {{ $nomor_surat }}
        </div>

        <p class="text-justify" style="margin-top: 15px; margin-bottom: 15px;">
            Yang bertanda tangan di bawah ini Adalah:
        </p>

        <!-- DATA MAHASISWA -->
        <table class="table-data" style="width: 95%;">
            <tr>
                <td width="30%">Nama</td>
                <td width="3%">:</td>
                <td class="text-bold">{{ $nama }}</td>
            </tr>
            <tr>
                <td>Tempat, Tanggal Lahir</td>
                <td>:</td>
                <td>{{ $tempat_lahir }}, {{ $tanggal_lahir }}</td>
            </tr>
            <tr>
                <td>NIM</td>
                <td>:</td>
                <td>{{ $nim }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>:</td>
                <td>{{ $alamat }}</td>
            </tr>
            <tr>
                <td>Nama Ortu</td>
                <td>:</td>
                <td>{{ $nama_ortu }}</td>
            </tr>
        </table>

        <p class="text-justify" style="text-indent: 0.5in; margin-top: 15px; margin-bottom: 15px;">
            Adalah Benar <strong>Mahasiswa Aktif</strong> Program Studi {{ $prodi_mhs }} Darullughah Wadda'wah 
            (UII DALWA) dan sedang mendapat tugas praktek Mengajar (<strong>SPM/<em>Mubta'ast</em></strong>) Pada 
            Dan Tempat Tugas Mengajarnya Adalah:
        </p>

        <!-- DATA TUGAS -->
        <table class="table-data" style="width: 95%;">
            <tr>
                <td width="30%">Tempat Tugas</td>
                <td width="3%">:</td>
                <td>{{ $tempat_tugas }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>:</td>
                <td>{{ $alamat_tugas }}</td>
            </tr>

            <tr>
                <td>Tahun</td>
                <td>:</td>
                <td>{{ $tahun }}</td>
            </tr>
            <tr>
                <td>Semester</td>
                <td>:</td>
                <td>{{ $semester }}</td>
            </tr>
        </table>

        <p class="text-justify" style="text-indent: 0.5in; margin-top: 20px; margin-bottom: 40px;">
            Demikian Surat Keterangan Ini Dibuat Dengan Sebenar-Benarnya Dan Dapat Dipertanggung Jawabkan.
        </p>

        <!-- DUA TANDA TANGAN (DOUBLE-SIGNATURE) -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 20px;">
            <tr>
                <!-- Kiri: Pengawas SPM -->
                <td width="45%" class="text-center" style="vertical-align: top;">
                    &nbsp;<br>
                    Pengawas SPM,
                    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: -25px; margin-bottom: -35px;">
                        <tr>
                            <td style="height: 140px; text-align: center; vertical-align: middle; padding: 0;">
                                <div style="position: relative; width: 100%; height: 140px;">
                                    @if(!empty($stempel))
                                    <img src="{{ $stempel }}" style="position: absolute; left: -10px; top: 5px; width: 110px; height: 110px; z-index: 2;">
                                    @endif
                                    
                                    @if($pengawas_ttd)
                                    <!-- <img src="{{ $pengawas_ttd }}" style="position: absolute; left: 0px; top: 30px; width: 170px; max-height: 80px; object-fit: contain; z-index: 1;"> -->
                                    @else
                                    <div style="position: absolute; left: 0; right: 0; top: 60px; z-index: 2;">
                                        <br>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>
                    <div style="margin-top: 5px;">
                        <strong><u>{{ $pengawas_nama }}</u></strong>
                    </div>
                </td>

                <!-- Spacer -->
                <td width="10%"></td>

                <!-- Kanan: Kaprodi -->
                <td width="45%" class="text-center" style="vertical-align: top;">
                    Bangil, {{ $tanggal_surat }}<br>
                    Kaprodi {{ $alias_prodi ?? $prodi_mhs }}
                    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: -25px; margin-bottom: -35px;">
                        <tr>
                            <td style="height: 140px; text-align: center; vertical-align: middle; padding: 0;">
                                <div style="position: relative; width: 100%; height: 140px;">
                                    @if(!empty($stempel))
                                    <img src="{{ $stempel }}" style="position: absolute; left: -10px; top: 5px; width: 110px; height: 110px; z-index: 2;">
                                    @endif
                                    
                                    @if($ttd)
                                    <img src="{{ $ttd }}" style="position: absolute; left: 0px; top: 30px; width: 170px; max-height: 80px; object-fit: contain; z-index: 1;">
                                    @else
                                    <div style="position: absolute; left: 0; right: 0; top: 60px; z-index: 2;">
                                        <br>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>
                    <div style="margin-top: 5px;">
                        <strong><u>{{ $nama_kepala_prodi }}</u></strong>
                    </div>
                    @if($nidn_kepala_prodi)
                    <div style="font-size: 10pt; margin-top: 2px;">
                        NIY: {{ $nidn_kepala_prodi }}
                    </div>
                    @endif
                </td>
            </tr>
        </table>

    </div>

</body>

</html>
