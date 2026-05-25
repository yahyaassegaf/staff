<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Ujian Komprehensif Diniyah</title>

    <style>
        @page {
            margin: 2cm 2cm 2cm 2cm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
        }

        /* KOP SURAT */
        .kop {
            margin-top: -2.1cm;
            margin-left: -1.5cm;
            margin-right: -1.5cm;
            margin-bottom: 15px;
        }

        .kop img {
            margin-top: 5px;
            width: 100%;
            max-height: 155px;
            object-fit: contain;
            display: block;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            /* text-align: right; */
            margin-right: 50cm;

        }

        .judul {
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            margin-top: 10px;
        }

        .nomor {
            margin-top: 5px;
            margin-bottom: 10px;
        }

        .paragraf {
            text-align: justify;
            text-indent: 1.25cm;
            /* alinea */
            margin-bottom: 12px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-left: 1.25cm;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        table.data td {
            padding: 2px 0;
            vertical-align: top;
        }

        table.data td.label {
            width: 170px;
        }

        table.ttd {
            width: 100%;
            margin-top: 50px;
        }

        .nama-ttd {
            margin-top: 70px;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <!-- KOP -->
    <div class="kop">
        <img src="{{ $kopBase64 }}" alt="Kop Surat UII Dalwa">
    </div>

    <!-- JUDUL -->
    <div class="text-center">
        <div class="judul">SURAT KETERANGAN UJIAN KOMPREHENSIF DINIYAH</div>
        <div class="nomor">
            Nomor: {{ $nomor_surat }}
        </div>
    </div>

    <!-- PEMBUKA -->
    <div class="paragraf">
        Yang bertanda tangan di bawah ini kami, Ketua / Koordinator Program Komprehensif
        Terpadu Universitas Islam Internasional Darullughah Wadda’wah Bangil Pasuruan
        menerangkan dengan sesungguhnya bahwa:
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
        Mahasiswa tersebut telah lulus semua mata kuliah bidang studi komprehensif terpadu
        (Al-Qur’an, Muhawaroh, Nahwu, Shorof, Qiroatul Kutub, dan Insya’).
    </div>

    <div class="paragraf">
        Adapun bukti-bukti dan keterangan tentang hal yang tersebut di atas sebagaimana
        terlampir, dan apabila di kemudian hari terdapat kesalahan pada catatan kami dan
        yang bersangkutan terbukti tidak sesuai dengan ketentuan di atas maka surat
        keterangan ini dapat dicabut kembali.
    </div>

    <div class="paragraf">
        Demikian surat keterangan ini kami buat dengan sebenar-benarnya sebagai persyaratan
        agar mahasiswa tersebut dapat mengikuti ujian skripsi.
    </div>

    <!-- TANDA TANGAN -->
    <table class="ttd" width="100%" cellpadding="0" cellspacing="0" style="margin-top:50px;">
        <tr>
            <td width="60%"></td>
            <td width="40%" class="text-center">
                Bangil, {{ $tanggal_surat }}<br>
                Ketua / Koordinator Komprehensif<br>

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
                            <!-- <img src="{{ $ttd }}" style="width:200px;"> -->
                        </td>
                    </tr>
                </table>

                <div class="nama-ttd" style="margin-top:4px;">
                    {{ $nama_penandatangan }}
                </div>
            </td>
        </tr>
    </table>

</body>

</html>
