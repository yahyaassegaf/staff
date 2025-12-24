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
            width: 100%;
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
            margin-bottom: 20px;
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
        <div class="text-center margin-bottom-20">
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

        <br><br>

        <!-- TANDA TANGAN -->
        <table>
            <tr>
                <td width="55%"></td>
                <td class="text-center">
                    Bangil, {{ $tanggal }}<br>
                    Kepala Prodi<br><br><br><br><br>
                    <strong>{{ $nama_kepala }}</strong><br>
                    NIY: {{ $nidn_kepala }}
                </td>
            </tr>
        </table>

    </div>

</body>

</html>