<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Pernyataan Verifikasi Nilai</title>

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
            margin-left: -1.5cm;
            margin-right: -1.5cm;
        }

        ol.numbered-list {
            margin: 0;
            padding-left: 25px;
            text-align: justify;
            margin-left: 30px;
        }

        ol.numbered-list li {
            margin-bottom: 10px;
            padding-left: 5px;
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
        <div class="judul">SURAT PERNYATAAN MELAKUKAN VERIFIKASI NILAI MAHASISWA</div>
        <div class="nomor">
            Nomor: {{ $nomor }}
        </div>
    </div>

    <!-- PEMBUKA -->
    <div class="paragraf">
        Yang bertanda tangan dibawah ini :
    </div>

    <!-- DATA PENANDATANGAN -->
    <table class="data">
        <tr>
            <td class="label">Nama</td>
            <td width="10">:</td>
            <td>{{ $nama_penandatangan }}</td>
        </tr>
        <tr>
            <td class="label">NIY</td>
            <td>:</td>
            <td>{{ $niy ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td>:</td>
            <td>{{ $jabatan }}</td>
        </tr>
    </table>

    <!-- ISI -->
    <div class="paragraf">
        Menyatakan bahwa:
    </div>

    <ol class="numbered-list">
        <li>Telah melakukan verifikasi terhadap data mahasiswa yang telah mendaftar atau mengajukan ujian skripsi.</li>
        <li>Benar mahasiswa atas nama {{ $nama_mahasiswa }} terdaftar sebagai mahasiswa aktif sejak tahun {{ $nim }} difakultas {{ $fakultas }} Program Studi {{ $prodi }}.</li>
        <li>Benar mahasiswa tersebut diatas telah mengikuti perkuliahan secara aktif dan sebagai bukti bahwa seluruh matakuliah yang terdaftar dalam kurikulum program studi tersebut diatas memiliki nilai.</li>
        <li>Data yang telah saya diverifikasi merupakan data asli dan sesuai dengan data yang terdapat dalam siakad mahasiswa tersebut diatas.</li>
        <li>Manakala data tersebut diatas terdapat tidak sesuaian dengan data aslinya di siakad mahaswa, maka semuanya menjadi tanggung jawab saya.</li>
    </ol>

    <div class="paragraf">
        Demikian surat pernyataan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
    </div>

    <!-- TANDA TANGAN -->
    <table class="ttd" width="100%" cellpadding="0" cellspacing="0" style="margin-top:50px;">
        <tr>
            <td width="60%"></td>

            <!-- BLOK KANAN -->
            <td width="40%" style="text-align:center;">
                Bangil, {{ $tanggal }}<br>
                {{ $staff }}<br>

                <!-- AREA TTD (OVERLAY AMAN) -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: -20px; margin-bottom: -25px;">
                    <tr>
                        <td style="height:115px; text-align:center; vertical-align:middle; padding:0;">
                            <div style="position: relative; width: 100%; height: 115px;">
                                @if(!empty($stempel))
                                <img src="{{ $stempel }}" style="position: absolute; left: -10px; top: 2px; width: 110px; height: 110px; z-index: 2;">
                                @endif
                                
                                @if(!empty($ttd))
                                <img src="{{ $ttd }}" style="position: absolute; left: 0px; top: 10px; width: 200px; z-index: 1;">

                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="nama-ttd" style="margin-top:4px;">
                    {{ $nama_penandatangan }}
                </div>
                NIY : {{ $niy ?? '-' }}
            </td>
        </tr>
    </table>
</body>

</html>
