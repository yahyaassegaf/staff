<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Mutasi</title>

    <style>
        /* WAJIB untuk DomPDF */
        @page {
            margin: 0cm 2cm 1cm 2cm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .judul {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            margin-top: 15px;
            letter-spacing: 0.5px;
        }

        .nomor {
            font-size: 12pt;
            margin-top: 2px;
            margin-bottom: 25px;
        }

        .paragraf {
            text-align: justify;
            text-indent: 1.25cm;
            margin-top: 12px;
            margin-bottom: 12px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
            margin-left: 55px;
            /* Aligned with standard indent */
        }

        table.data td {
            padding: 3px 0;
            vertical-align: top;
        }

        table.data td.label {
            width: 180px;
        }

        table.data td.separator {
            width: 15px;
            text-align: center;
        }

        table.data td.value {
            padding-right: 60px;
            /* Prevent value text overflowing to the right edge */
        }

        table.ttd {
            width: 100%;
            margin-top: 40px;
        }

        .nama-ttd {
            margin-top: 4px;
            font-weight: bold;
            text-decoration: underline;
            font-size: 12pt;
        }

        .nidn-ttd {
            font-weight: bold;
            margin-top: 2px;
            font-size: 12pt;
        }

        .kop img {
            margin-top: 0px;
            width: 100%;
            height: auto;
            display: block;
        }

        .kop {
            margin-top: 0.1cm;
            /* naik ke atas */
            margin-left: -1.5cm;
            /* tembus margin kiri */
            margin-right: -1.5cm;
            /* tembus margin kanan */
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="kop">
        @if(!empty($kopBase64))
        <img src="{{ $kopBase64 }}" alt="Kop Surat">
        @endif
    </div>

    <!-- JUDUL -->
    <div class="text-center">
        <div class="judul">SURAT KETERANGAN MUTASI</div>
        <div class="nomor">
            Nomor : {{ $nomor }}
        </div>
    </div>

    <!-- PEMBUKA -->
    <div class="paragraf">
        Yang bertanda tangan di bawah ini, Dekan {{ $nama_fakultas }} Universitas Islam Internasional
        Darullughah Wadda'wah Bangil Pasuruan Jawa Timur, menerangkan dengan sesungguhnya bahwa :
    </div>

    <!-- DATA MAHASISWA -->
    <table class="data">
        <tr>
            <td class="label">Nama</td>
            <td class="separator">:</td>
            <td class="value">{{ strtoupper($nama ?? '') }}</td>
        </tr>
        <tr>
            <td class="label">NIM</td>
            <td class="separator">:</td>
            <td class="value">{{ $nim }}</td>
        </tr>
        <tr>
            <td class="label">TTL</td>
            <td class="separator">:</td>
            <td class="value">{{ strtoupper($tempat_lahir ?? '') }}, {{ strtoupper($tanggal_lahir ?? '') }}</td>
        </tr>
        <tr>
            <td class="label">Jurusan/Program Studi</td>
            <td class="separator">:</td>
            <td class="value">{{ $jurusan_prodi }}</td>
        </tr>
        <tr>
            <td class="label">Alamat</td>
            <td class="separator">:</td>
            <td class="value">{{ $alamat }}</td>
        </tr>
    </table>

    <!-- ISI -->
    <div class="paragraf">
        Adalah benar-benar mahasiswa Universitas Islam Internasional Darullughah Wadda'wah
        yang terakhir terdaftar pada semester {{ $semester }} Tahun Akademik {{ $tahun_akademik }}
        akan pindah ke {{ $universitas_tujuan }}.
    </div>

    <div class="paragraf">
        Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.
    </div>

    <!-- TANDA TANGAN -->
    <table class="ttd" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td width="55%"></td>

            <!-- BLOK KANAN -->
            <td width="45%" style="text-align:center; vertical-align: top;">
                Bangil, {{ $tanggal }}<br>
                Dekan {{ $nama_fakultas }}<br>

                <!-- AREA TTD (OVERLAY AMAN) -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: -20px; margin-bottom: -20px;">
                    <tr>
                        <td style="height:60px; text-align:center; vertical-align:middle; padding:0;">
                            <div style="position: relative; width: 100%; height: 110px;">
                                @if(!empty($stempel))
                                <img src="{{ $stempel }}" style="position: absolute; left: 30px; top: 0px; width: 110px; height: 110px; z-index: 2;">
                                @endif

                                @if(!empty($ttd))
                                <img src="{{ $ttd }}" style="position: absolute; left: 40px; top: 10px; width: 200px; z-index: 1;">
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="nama-ttd">
                    {{ $dekan }}
                </div>
                <!-- <div class="nidn-ttd">
                    NIDN {{ $nidn_dosen }}
                </div> -->
            </td>
        </tr>
    </table>
</body>

</html>