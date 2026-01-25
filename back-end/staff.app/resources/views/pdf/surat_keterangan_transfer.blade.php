<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Transfer</title>

    <style>
        /* WAJIB untuk DomPDF */
        @page {
            margin: 1cm 2cm 1cm 2cm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .header-univ {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .judul {
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            margin-top: 20px;
        }

        .nomor {
            margin-bottom: 25px;
        }

        .paragraf {
            text-align: justify;
            text-indent: 1.25cm;
            margin-bottom: 10px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
            margin-left: 30px;
            /* indent seperti Word */
        }

        table.data td {
            padding: 2px 0;
            vertical-align: top;
        }

        table.data td.label {
            width: 180px;
        }

        table.ttd {
            width: 100%;
            margin-top: 40px;
        }

        .nama-ttd {
            margin-top: 10px;
            font-weight: bold;
            text-decoration: underline;
        }

        .kop img {
            margin-top: 5px;
            width: 100%;
            max-height: 155px;
            object-fit: contain;
            display: block;
        }

        .text-left {
            text-align: left;
            margin-left: 30px;
        }

        .kop {
            margin-top: -1.1cm;
            /* naik ke atas */
            margin-left: -1.5cm;
            /* tembus margin kiri */
            margin-right: -1.5cm;
            /* tembus margin kanan */
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="kop">
        <img src="{{ $kopBase64 }}" alt="Kop Surat">
    </div>

    <!-- JUDUL -->
    <div class="text-center">
        <div class="judul">SURAT KETERANGAN TRANSFER</div>
        <div class="nomor">
            Nomor: {{ $nomor }}
        </div>
    </div>

    <!-- PEMBUKA -->
    <div class="paragraf">
        Yang bertanda tangan di bawah ini, Dekan {{ $nama_fakultas }}
        Universitas Islam Internasional Darullughah Wadda'wah Bangil,
        Pasuruan, Jawa Timur, menerangkan dengan sesungguhnya bahwa:
    </div>

    <!-- DATA MAHASISWA -->
    <table class="data">
        <tr>
            <td class="label">Nama</td>
            <td width="10">:</td>
            <td>{{ $nama }}</td>
        </tr>
        <tr>
            <td class="label">Tempat, Tanggal Lahir</td>
            <td>:</td>
            <td>{{ $tanggal_lahir }}</td>
        </tr>
        <tr>
            <td class="label">NIM</td>
            <td>:</td>
            <td>{{ $nim }}</td>
        </tr>
        <tr>
            <td class="label">Jurusan / Program Studi</td>
            <td>:</td>
            <td>{{ $jurusan_prodi }}</td>
        </tr>
    </table>

    <!-- ISI -->
    <div class="paragraf">
        Adalah benar-benar mahasiswa Universitas Islam Internasional
        Darullughah Wadda'wah yang terakhir terdaftar pada Semester {{ $semester }}
        Tahun Akademik {{ $tahun_akademik }} dan yang bersangkutan pindah dari
        Universitas Islam Internasional Darullughah Wadda'wah
        untuk melanjutkan studi ke perguruan tinggi lain.
    </div>

    <div class="paragraf">
        Demikian surat keterangan ini dibuat untuk dipergunakan
        sebagaimana mestinya.
    </div>

    <!-- TANDA TANGAN -->
    <table class="ttd" width="100%" cellpadding="0" cellspacing="0" style="margin-top:50px;">
        <tr>
            <td width="60%"></td>

            <!-- BLOK KANAN -->
            <td width="40%" style="text-align:center;">
                Bangil, {{ $tanggal }}<br>
                Dekan {{ $nama_fakultas }}<br>

                <!-- AREA TTD (OVERLAY AMAN) -->
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="
            height:70px;
            text-align:center;
            vertical-align:middle;
            background-image: url('{{ $stempel }}');
            background-repeat: no-repeat;
            background-position: 20% 60%;
            background-size: 90px 90px;
        ">
                            <img src="{{ $ttd }}" style="width:250px;">
                        </td>
                    </tr>
                </table>
                <div class="nama-ttd" style="margin-top:4px;">
                    {{ $dekan }}
                </div>
                <!-- NIDN : {{ $nidn_kepala }} -->
            </td>
        </tr>
    </table>
</body>

</html>