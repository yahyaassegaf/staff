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
            SURAT KETERANGAN
        </div>
        <div class="text-center margin-bottom-20">
            Nomor: {{ $nomor }}
        </div>

        <p class="text-justify">
            Yang bertanda tangan di bawah ini:
        </p>

        <!-- DATA PEJABAT -->
        <table class="margin-bottom-20">
            <tr>
                <td width="30%">Nama</td>
                <td width="5%">:</td>
                <td>{{ $nama_kepala }}</td>
            </tr>
            <tr>
                <td>NIY</td>
                <td>:</td>
                <td>{{ $nidn_kepala }}</td>
            </tr>
        </table>

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
            {{ $alasan }} untuk periode bulan {{ $periode_bulan }} yang diverifikasi oleh {{ $nama_staff }}.
        </p>

        <p class="text-justify">
            Demikian surat keterangan ini kami sampaikan, semoga dapat dipergunakan sebagaimana mestinya.
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