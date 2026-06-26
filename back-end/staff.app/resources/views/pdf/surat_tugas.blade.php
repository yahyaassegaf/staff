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
            margin-top: 1cm;
            margin-left: -1cm;
            margin-right: -1cm;
        }

        .kop img {
            width: 100%;
            height: auto;
            display: block;
        }

        /* ===== KONTEN ===== */
        .content {
            padding-right: 2.5cm;
            padding-bottom: 1cm;
            padding-left: 2.5cm;
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
            padding: 3px 0;
        }

        .margin-bottom-20 {
            margin-bottom: 10px;
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

        <div class="text-center text-bold text-underline">
            SURAT TUGAS
        </div>
        <div class="text-center margin-bottom-10">
            Nomor: {{ $nomor }}
        </div>

        <p class="text-justify">
            Yang bertanda tangan di bawah ini menerangkan bahwa:
        </p>

        <!-- DATA DOSEN -->
        <table class="margin-bottom-20">
            <tr>
                <td width="30%">Nama Dosen</td>
                <td width="5%">:</td>
                <td>{{ $nama_dosen }}</td>
            </tr>
            <tr>
                <td>Alamat Dosen</td>
                <td>:</td>
                <td>{{ $alamat_dosen }}</td>
            </tr>
            <tr>
                <td>Tugas Dosen</td>
                <td>:</td>
                <td>{{ $tugas_dosen }}</td>
            </tr>
        </table>

        <p class="text-justify">
            Untuk melaksanakan tugas yaitu {{ $tugasnya }} terhadap mahasiswa:
        </p>

        <!-- DATA MAHASISWA -->
        <table class="margin-bottom-20">
            <tr>
                <td width="30%">Nama Mahasiswa</td>
                <td width="5%">:</td>
                <td>{{ $nama_mhs }}</td>
            </tr>
            <tr>
                <td>NIM / NIK</td>
                <td>:</td>
                <td>{{ $nim_nik }}</td>
            </tr>
            <tr>
                <td>Fakultas / Prodi</td>
                <td>:</td>
                <td>{{ $fakultas_prodi }}</td>
            </tr>
            <tr>
                <td>Judul Skripsi</td>
                <td>:</td>
                <td>{{ $judul_skripsi }}</td>
            </tr>
        </table>

        <p class="text-justify">
            Masa penugasan ini berlaku selama {{ $masa_penugasan }}.
        </p>

        <p class="text-justify">
            Demikian surat tugas ini kami sampaikan agar dapat dilaksanakan dengan penuh tanggung jawab.
        </p>

        <table class="ttd" width="100%" cellpadding="0" cellspacing="0" style="margin-top:10px;">
            <tr>
                <td width="60%"></td>
                <td width="40%" class="text-center">
                    Bangil, {{ $tanggal }}<br>
                    Kepala Prodi
                    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: -20px; margin-bottom: -30px;">
                        <tr>
                            <td style="height: 155px; text-align:center; vertical-align:middle; padding:0;">
                                <div style="position: relative; width: 100%; height: 155px;">
                                    @if(!empty($stempel))
                                    <img src="{{ $stempel }}" style="position: absolute; left: -10px; top: 5px; width: 115px; height: 115px; z-index: 2;">
                                    @endif
                                    
                                    @if(!empty($ttd))
                                    <img src="{{ $ttd }}" style="position: absolute; left: 0px; top: 20px; width: 200px; z-index: 1;">

                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class="nama-ttd">
                        <strong>{{ $nama_kepala }}</strong>
                    </div>
                    NIY: {{ $nidn_kepala }}
                </td>
            </tr>
        </table>

    </div>

</body>

</html>
