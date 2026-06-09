<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Izin Penelitian</title>

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

        .nomor {
            margin-bottom: 20px;
        }

        .alamat {
            margin-bottom: 20px;
        }

        .salam {
            margin-bottom: 15px;
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
            margin-top: -1cm;
            /* naik ke atas */
            margin-left: -1.5cm;
            /* tembus margin kiri */
            margin-right: -1.5cm;
            /* tembus margin kanan */
        }

        .penutup {
            margin-top: 20px;
            text-align: justify;
            text-indent: 1.25cm;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="kop">
        <img src="{{ $kopBase64 }}" alt="Kop Surat">
    </div>

    <!-- NOMOR SURAT -->
    <div class="nomor">
        Nomor &nbsp;&nbsp;: {{ $nomor }}<br>
        Lamp. &nbsp;&nbsp;&nbsp;: -<br>
        Hal &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Surat Izin Penelitian
    </div>

    <!-- TUJUAN -->
    <div class="alamat">
        Kepada Yth.<br>
        {{ $kepada }}<br>
        di Tempat
    </div>

    <!-- SALAM -->
    <div class="salam">
        Assalamu'alaikum Warahmatullahi Wabarakatuh,
    </div>

    <!-- PARAGRAF PEMBUKA -->
    <div class="paragraf">
        Bersamaan dengan surat ini mohon dengan hormat, agar mahasiswa/i berikut ini:
    </div>

    <!-- DATA MAHASISWA -->
    <table class="data">
        <tr>
            <td class="label">Nama</td>
            <td width="10">:</td>
            <td>{{ $nama }}</td>
        </tr>
        <tr>
            <td class="label">NIM</td>
            <td>:</td>
            <td>{{ $nim }}</td>
        </tr>
        <tr>
            <td class="label">Semester</td>
            <td>:</td>
            <td>{{ $semester }}</td>
        </tr>
        <tr>
            <td class="label">Fakultas / Prodi</td>
            <td>:</td>
            <td>{{ $fakultas_name }} / {{ $prodi_name }}</td>
        </tr>
        <tr>
            <td class="label">Dari Tanggal</td>
            <td>:</td>
            <td>{{ $dari_tanggal }} s/d Selesai</td>
        </tr>
    </table>

    <!-- PARAGRAF ISI -->
    <div class="paragraf">
        Diberikan kesempatan dan izinnya untuk melaksanakan penelitian di Lembaga Pendidikan
        yang Bapak / Ibu pimpin, sebagai prasyarat penyelesaian studi atau perkuliahan di
        Universitas Islam Internasional Darullughah Wadda'wah Bangil, Pasuruan.
    </div>

    <!-- PENUTUP -->
    <div class="penutup">
        Demikian surat permohonan ini, atas pertimbangan dan persetujuannya kami sampaikan
        terima kasih, jazakumullaahu khairan, insya Allaah, aamiin.
    </div>
    <div class="salam">
        Wassalamu'alaikum warahmatullahi wabarakatuh.
    </div>

    <!-- TANDA TANGAN -->
    <table class="ttd" width="100%" cellpadding="0" cellspacing="0" style="margin-top:50px;">
        <tr>
            <td width="60%"></td>

            <!-- BLOK KANAN -->
            <td width="40%" style="text-align:center;">
                Bangil, {{ $tanggal }}<br>
                Dekan Fakultas {{ $fakultas_name }}<br>

                <!-- AREA TTD (OVERLAY AMAN) -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: -20px; margin-bottom: -25px;">
                    <tr>
                        <td style="height:115px; text-align:center; vertical-align:middle; padding:0;">
                            <div style="position: relative; width: 100%; height: 115px;">
                                @if(!empty($stempel))
                                <img src="{{ $stempel }}" style="position: absolute; left: -10px; top: 10px; width: 110px; height: 110px; z-index: 2;">
                                @endif
                                @if(!empty($ttd))
                                <img src="{{ $ttd }}" style="position: absolute; left: 0px; top: 10px; width: 200px; z-index: 1;">
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="nama-ttd" style="margin-top:4px;">
                    {{ $nama_dekan }}
                </div>
            </td>
        </tr>
    </table>
</body>

</html>