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
            margin-left: -1.5cm;
            margin-right: -1.5cm;
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

        .nama-ttd {
            margin-top: -15px;
            font-weight: bold;
            text-decoration: underline;
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
            SURAT KETERANGAN
        </div>
        <div class="text-center margin-bottom-10">
            Nomor: {{ $nomor }}
        </div>

        <p class="text-justify">
            Menerangkan bahwa:
        </p>

        <!-- DATA MAHASISWA -->
        <table class="margin-bottom-20">
            <tr>
                <td width="30%">Nama Mahasiswa</td>
                <td width="5%">:</td>
                <td>{{ $nama_mahasiswa }}</td>
            </tr>
            <tr>
                <td>NIM</td>
                <td>:</td>
                <td>{{ $nim }}</td>
            </tr>
            <tr>
                <td>Jenis Kelamin</td>
                <td>:</td>
                <td>{{ $jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>
            <tr>
                <td>Program Studi</td>
                <td>:</td>
                <td>{{ $prodi }}</td>
            </tr>
        </table>

        <p class="text-justify">
            Mahasiswa tersebut {{ $alasan }} untuk periode bulan {{ $periode_bulan }} yang diverifikasi oleh {{ $nama_staff }}.
        </p>

        <p class="text-justify">
            Demikian surat keterangan ini kami sampaikan, semoga dapat dipergunakan sebagaimana mestinya.
        </p>

        <!-- TANDA TANGAN -->
        <table class="ttd" width="100%" cellpadding="0" cellspacing="0" style="margin-top:50px;">
            <tr>
                <td width="60%"></td>

                <!-- BLOK KANAN -->
                <td width="40%" style="text-align:center;">
                    Bangil, {{ $tanggal }}<br>
                    Kepala Prodi
                    <!-- AREA TTD (OVERLAY AMAN) -->
                    <table width="100%" cellpadding="0" cellspacing="0"
                        style="
                        margin-top: -15px;
                        margin-bottom: -20px;
                        padding: 0;
                        line-height: 0;">
                        <tr>
                            <td style="height: 125px; text-align:center; vertical-align:middle; padding: 0;">
                                <div style="position: relative; width: 100%; height: 125px;">
                                    @if(!empty($stempel))
                                    <img src="{{ $stempel }}" style="position: absolute; left: -10px; top: 5px; width: 110px; height: 110px; z-index: 2;">
                                    @endif
                                    
                                    @if(!empty($ttd))
                                    <img src="{{ $ttd }}" style="position: absolute; left: 0px; top: 15px; width: 200px; z-index: 1;">

                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>
                    <div class="nama-ttd">
                        {{ $nama_kepala }}
                    </div>
                    <!-- NIY: {{ $nidn_kepala }} -->
                </td>
            </tr>
        </table>

    </div>

</body>

</html>