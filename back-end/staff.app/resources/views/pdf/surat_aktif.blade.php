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
            font-size: 9pt;
            line-height: 1.6;
            color: #000;
            margin: 0;
        }

        /* ===== KOP SURAT ===== */
        .kop {
            width: 100%;
        }

        .kop img {
            width: 100%;
            height: auto;
            display: block;
        }

        /* ===== KONTEN ===== */
        .content {
            /* dikurangi */
            padding-right: 2cm;
            padding-bottom: 1cm;
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
            padding: 2px 0;
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

        <div class="text-center" style="margin-bottom:25px;">
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
                <td>{{ $nama_kepala_prodi }}</td>
            </tr>
            <tr>
                <td>NIY</td>
                <td>:</td>
                <td>{{ $nidn_kepala_prodi }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>:</td>
                <td>Kepala Prodi Pendidikan Bahasa Arab</td>
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
                <td>NIK</td>
                <td>:</td>
                <td>{{ $nik }}</td>
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
            Merupakan mahasiswa/i pada Program Studi Pendidikan Bahasa Arab Fakultas Tarbiyah
            Institut Agama Islam Darullughah Wadda’wah Bangil, dan orang tua dari mahasiswa/i tersebut adalah:
        </p>

        <!-- DATA ORANG TUA -->
        <table>
            <tr>
                <td width="30%">Nama Orang Tua</td>
                <td width="5%">:</td>
                <td>{{ $nama_ortu }}</td>
            </tr>
            <tr>
                <td>NIK</td>
                <td>:</td>
                <td>{{ $nik_ortu }}</td>
            </tr>
            <tr>
                <td>NIP</td>
                <td>:</td>
                <td>{{ $nip_ortu }}</td>
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

        <br><br>

        <!-- TANDA TANGAN -->
        <table>
            <tr>
                <td width="60%"></td>
                <td class="text-center">
                    Bangil,{{ $tanggal_surat }}<br>
                    Kepala Prodi {{ $prodi_mhs }}<br><br><br><br>
                    <strong>{{ $nama_kepala_prodi }}</strong><br>
                    NIY: {{ $nidn_kepala_prodi }}
                </td>
            </tr>
        </table>

    </div>

</body>

</html>