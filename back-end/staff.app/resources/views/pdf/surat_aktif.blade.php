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
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
            margin: 0;
        }

        /* ===== KOP SURAT ===== */
        .kop {
            margin-top: 0.1cm;
            width: 100%;
        }

        .kop img {
            /* margin-top: 10px; */
            width: 100%;
            height: auto;
            display: block;
        }

        /* ===== KONTEN ===== */
        .content {
            padding-right: 2cm;
            padding-left: 2cm;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-justify {
            text-align: justify;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
            padding: 1px 0;
        }
    </style>
</head>

<body>

    <!-- KOP SURAT -->
    <div class="kop">
        <img src="{{ $kopBase64 }}" alt="Kop Surat">
    </div>

    <!-- ISI SURAT -->
    <div class="content">

        <div class="text-center" style="font-weight:bold; text-decoration:underline;">
            SURAT KETERANGAN AKTIF MAHASISWA
        </div>

        <div class="text-center" style="margin-bottom:6px;">
            Nomor : {{ $nomor_surat }}
        </div>

        <p>Assalamu’alaikum Warahmatullaahi Wabarakatuh,</p>

        <p class="text-justify">
            Kami yang bertanda tangan di bawah ini:
        </p>

        <!-- DATA PEJABAT -->
        <table>
            <tr>
                <td width="30%">Nama</td>
                <td width="5%">:</td>
                <td>{{ $dekan }}</td>
            </tr>
            <tr>
                <td>NIY</td>
                <td>:</td>
                <td>{{ $nidn_dekan }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>:</td>
                <td>Dekan Fakultas {{ $fakultas }}</td>
            </tr>
            <tr>
                <td>Pada</td>
                <td>:</td>
                <td>Universitas Islam Internasional Darullughah Wadda’wah Bangil</td>
            </tr>
        </table>

        <p class="text-justify">
            Menerangkan atau menyampaikan bahwa data berikut:
        </p>

        <!-- DATA MAHASISWA -->
        <table>
            <tr>
                <td width="30%">Nama</td>
                <td width="5%">:</td>
                <td>{{ $nama }}</td>
            </tr>
            <tr>
                <td>NIM</td>
                <td>:</td>
                <td>{{ $nim }}</td>
            </tr>
            <tr>
                <td>Tempat, Tanggal Lahir</td>
                <td>:</td>
                <td>{{ $tempat_lahir }},{{ $tanggal_lahir }}</td>
            </tr>
            <tr>
                <td>Program Studi / Semester</td>
                <td>:</td>
                <td>{{ $nama_prodi }} / {{ $semester }}</td>
            </tr>
            <tr>
                <td>Tahun Akademik</td>
                <td>:</td>
                <td>{{ $tahun_akademik }}</td>
            </tr>
        </table>

        <!-- KALIMAT TAMBAHAN -->
        <p class="text-justify">
            Merupakan mahasiswa/i pada Program Studi {{ $nama_prodi }} Fakultas {{ $fakultas }}
            Universitas Islam Internasional Darullughah Wadda’wah Bangil, dan orang tua dari mahasiswa/i tersebut adalah:
        </p>

        <!-- DATA ORANG TUA -->
        <table>
            <tr>
                <td width="30%">Nama Orang Tua</td>
                <td width="5%">:</td>
                <td>{{ $nama_ortu }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>:</td>
                <td>{{ $alamat_ortu }}</td>
            </tr>
            <tr>
                <td>Contact Person</td>
                <td>:</td>
                <td>{{ $hp_ortu }}</td>
            </tr>
        </table>

        <p class="text-justify">
            Demikian surat keterangan ini kami sampaikan, semoga dapat dipergunakan sebagaimana
            mestinya dan apabila terdapat kekeliruan akan diperbaiki sebagaimana mestinya.
        </p>
        <!-- TANDA TANGAN -->
        <table class="ttd" width="100%" cellpadding="0" cellspacing="0" style="margin-top:50px;">
            <tr>
                <td width="60%"></td>
                <td width="40%" class="text-center">
                    Bangil, {{ $tanggal_surat }}<br>
                    Dekan Fakultas {{ $fakultas }}<br>

                    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: -20px; margin-bottom: -25px;">
                        <tr>
                            <td style="height: 120px; text-align: center; vertical-align: middle; padding: 0;">
                                <div style="position: relative; width: 100%; height: 120px;">
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

                    <div class="nama-ttd" style="margin-top:4px;">
                        <strong style="text-decoration: underline;">{{ $dekan }}</strong>
                    </div>
                    <!-- NIY: {{ $nidn_dekan }} -->
                </td>
            </tr>
        </table>

    </div>

</body>

</html>