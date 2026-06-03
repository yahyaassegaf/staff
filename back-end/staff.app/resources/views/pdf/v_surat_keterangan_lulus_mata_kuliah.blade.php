<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan</title>

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
            margin-top: 0;
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
            margin-top: -1cm;
            /* mentok ke atas batas margin kertas (1cm) */
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
        <div class="judul">SURAT KETERANGAN LULUS MATA KULIAH</div>
        <div class="nomor">
            Nomor: {{ $nomor_surat }}
        </div>
    </div>

    <!-- PEMBUKA -->
    <div class="paragraf">
        Yang bertanda tangan di bawah ini kami, Dekan Fakultas {{ $fakultas  }}
        Universitas Islam Internasional Darullughah Wadda’wah Bangil Pasuruan menerangkan
        dengan sesungguhnya bahwa:
    </div>

    <!-- DATA MAHASISWA -->
    <table class="data">
        <tr>
            <td class="label">Nama Lengkap</td>
            <td width="10">:</td>
            <td>{{ $nama }}</td>
        </tr>
        <tr>
            <td class="label">Tempat, Tgl. Lahir</td>
            <td>:</td>
            <td>{{ $tempat_lahir }}, {{ $tanggal_lahir }}</td>
        </tr>
        <tr>
            <td class="label">NIM</td>
            <td>:</td>
            <td>{{ $nim }}</td>
        </tr>
        <tr>
            <td class="label">Fakultas / Prodi</td>
            <td>:</td>
            <td>{{ $fakultas }} / {{ $prodi }}</td>
        </tr>
        <tr>
            <td class="label">Alamat Rumah</td>
            <td>:</td>
            <td>{{ $alamat }}</td>
        </tr>
        <tr>
            <td class="label">Kelas</td>
            <td>:</td>
            <td>{{ $kelas }}</td>
        </tr>
    </table>

    <!-- ISI -->
    <div class="paragraf">
        Mahasiswa tersebut benar-benar aktif dan telah memenuhi segala persyaratan akademik
        untuk mengikuti ujian skripsi antara lain:
    </div>

    <div class="text-left">
        1. Telah lulus semua mata kuliah.<br>
        2. Telah memberikan sumbangan / wakaf buku untuk perpustakaan Dalwa.
    </div>

    <div class="paragraf">
        Adapun bukti-bukti dan keterangan tentang hal tersebut di atas sebagaimana terlampir,
        dan apabila di kemudian hari terdapat kesalahan pada catatan kami dan yang bersangkutan
        terbukti tidak sesuai dengan ketentuan di atas maka surat keterangan ini dapat dicabut kembali.
    </div>

    <div class="paragraf">
        Demikian surat keterangan ini kami buat dengan sebenar-benarnya sebagai persyaratan agar
        mahasiswa tersebut dapat mengikuti ujian skripsi.
    </div>
    <!-- AREA TTD & STEMPEL -->
    <!-- TANDA TANGAN -->
    <table class="ttd" width="100%" cellpadding="0" cellspacing="0" style="margin-top:50px;">
        <tr>
            <td width="60%"></td>

            <!-- BLOK KANAN -->
            <td width="40%" style="text-align:center;">
                Bangil, {{ $tanggal_surat }}<br>
                Dekan Fakultas {{ $fakultas }}<br>

                <!-- AREA TTD (OVERLAY AMAN) -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: -20px; margin-bottom: -25px;">
                    <tr>
                        <td style="
            height:115px;
            text-align:center;
            vertical-align:middle;
            /* background-image: url('{{ $stempel }}'); */
            background-repeat: no-repeat;
            background-position: 20% 50%;
            background-size: 110px 110px;
        ">
                            <!-- <img src="{{ $ttd }}" style="width:280px;"> -->
                        </td>
                    </tr>
                </table>
                <div class="nama-ttd" style="margin-top:4px;">
                    {{ $dekan }}
                </div>
                NIDN : {{ $nidn_dekan }}
            </td>
        </tr>
    </table>
</body>

</html>