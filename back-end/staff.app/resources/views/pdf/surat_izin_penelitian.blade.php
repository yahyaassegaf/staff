<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <style>
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

        .kop {
            width: 100%;
        }

        .kop img {
            width: 100%;
            height: auto;
            display: block;
        }

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

    <div class="kop">
        @if(isset($kopBase64))
        <img src="{{ $kopBase64 }}" alt="Kop Surat">
        @endif
    </div>

    <div class="content">

        <div class="text-center text-bold text-underline">
            SURAT IZIN PENELITIAN
        </div>
        <div class="text-center margin-bottom-20">
            Nomor: {{ $nomor }}
        </div>

        <p class="text-justify">
            Yang bertanda tangan di bawah ini menerangkan bahwa:
        </p>

        <table class="margin-bottom-20">
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
                <td>Semester</td>
                <td>:</td>
                <td>{{ $semester }}</td>
            </tr>
            <tr>
                <td>Fakultas / Prodi</td>
                <td>:</td>
                <td>{{ $fakultas_name }} / {{ $prodi_name }}</td>
            </tr>
        </table>

        <p class="text-justify">
            Adalah mahasiswa yang diberikan izin untuk melaksanakan penelitian terhitung mulai tanggal {{ $dari_tanggal }} di lingkungan institusi kami.
        </p>

        <p class="text-justify">
            Demikian surat izin ini dibuat untuk dipergunakan sebagaimana mestinya.
        </p>

        <br><br>

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