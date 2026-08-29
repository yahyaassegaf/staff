<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan</title>

    <style>
        /* WAJIB untuk DomPDF */
        @page {
            margin: 0cm 2cm 1cm 2cm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
        }

        .text-center {
            text-align: center;
        }

        .judul {
            font-weight: bold;
            font-size: 12pt;
            text-decoration: underline;
            margin-top: 15px;
        }

        .nomor {
            font-size: 10pt;
            margin-bottom: 30px;
        }

        .paragraf {
            text-align: justify;
            margin-bottom: 15px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 25px;
        }

        table.data td {
            padding: 4px 0;
            vertical-align: top;
        }

        table.data td.label {
            width: 90px;
        }

        table.data td.separator {
            width: 15px;
            text-align: center;
        }

        table.ttd {
            width: 100%;
            margin-top: 40px;
        }

        .nama-ttd {
            margin-top: 5px;
        }

        .kop img {
            margin-top: 0px;
            width: 100%;
            height: auto;
            display: block;
        }

        .kop {
            margin-top: 0.1cm;
            margin-left: -1.5cm;
            margin-right: -1.5cm;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    <!-- HALAMAN 1: TERDAFTAR -->
    <div class="kop">
        @if(!empty($kopBase64))
        <img src="{{ $kopBase64 }}" alt="Kop Surat">
        @endif
    </div>

    <div class="text-center">
        <div class="judul">SURAT KETERANGAN</div>
        <div class="nomor">
            {{ $nomor_surat }}
        </div>
    </div>

    <div class="paragraf">
        Menerangkan bahwa :
    </div>

    <table class="data">
        <tr>
            <td class="label">Nama</td>
            <td class="separator">:</td>
            <td class="value">{{ $nama ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">NIM</td>
            <td class="separator">:</td>
            <td class="value">{{ $nim }}</td>
        </tr>
        <tr>
            <td class="label">Prodi</td>
            <td class="separator">:</td>
            <td class="value">{{ $prodi }}</td>
        </tr>
    </table>

    <div class="paragraf">
        Mahasiswa tersebut <strong>telah terdaftar sebagai peserta ujian skripsi</strong> pada priode bulan {{ strtolower(\Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('F Y')) }}.
    </div>

    <div class="paragraf">
        Demikian surat keterangan ini dibuat, untuk dapat digunakan sebagai syarat mendaftar di Program Magister Pascasarjana UII Dalwa.
    </div>

    <table class="ttd" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td width="55%"></td>
            <td width="45%" style="text-align:left; padding-left: 20px;">
                Pasuruan, {{ $tanggal_surat }}<br>
                Staff Prodi {{ $alias_prodi }}<br>

                <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: -5px; margin-bottom: -15px;">
                    <tr>
                        <td style="height:120px; text-align:left; vertical-align:middle; padding:0;">
                            <div style="position: relative; width: 100%; height: 120px;">
                                @if(!empty($stempel))
                                <img src="{{ $stempel }}" style="position: absolute; left: -30px; top: 5px; width: 95px; height: 95px; z-index: 2;">
                                @endif

                                @if(!empty($ttd))
                                <img src="{{ $ttd }}" style="position: absolute; left: -15px; top: 15px; width: 175px; z-index: 1;">

                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="nama-ttd">
                    {{ $nama_kepala_prodi }}
                </div>
</td>
        </tr>
    </table>

    <div class="page-break"></div>

    <!-- HALAMAN 2: LULUS -->
    <div class="kop">
        @if(!empty($kopBase64))
        <img src="{{ $kopBase64 }}" alt="Kop Surat">
        @endif
    </div>

    <div class="text-center">
        <div class="judul">SURAT KETERANGAN</div>
        <div class="nomor">
            {{ $nomor_surat }}
        </div>
    </div>

    <div class="paragraf">
        Menerangkan bahwa :
    </div>

    <table class="data">
        <tr>
            <td class="label">Nama</td>
            <td class="separator">:</td>
            <td class="value">{{ $nama ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">NIM</td>
            <td class="separator">:</td>
            <td class="value">{{ $nim }}</td>
        </tr>
        <tr>
            <td class="label">Prodi</td>
            <td class="separator">:</td>
            <td class="value">{{ $prodi }}</td>
        </tr>
    </table>

    <div class="paragraf">
        Mahasiswa tersebut <strong>telah melaksanakan ujian skripsi dan dinyatakan lulus</strong>.
    </div>

    <div class="paragraf">
        Demikian surat keterangan ini dibuat, untuk dapat digunakan sebagai syarat mendaftar di Program Magister Pascasarjana UII Dalwa.
    </div>

    <table class="ttd" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td width="55%"></td>
            <td width="45%" style="text-align:left; padding-left: 20px;">
                Pasuruan, {{ $tanggal_surat }}<br>
                Staff Prodi {{ $alias_prodi }}<br>

                <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: -5px; margin-bottom: -15px;">
                    <tr>
                        <td style="height:120px; text-align:left; vertical-align:middle; padding:0;">
                            <div style="position: relative; width: 100%; height: 120px;">
                                @if(!empty($stempel))
                                <img src="{{ $stempel }}" style="position: absolute; left: -30px; top: 5px; width: 95px; height: 95px; z-index: 2;">
                                @endif

                                @if(!empty($ttd))
                                <img src="{{ $ttd }}" style="position: absolute; left: -15px; top: 20px; max-width:200px; max-height: 80px; object-fit: contain; z-index: 1;">

                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="nama-ttd">
                    {{ $nama_kepala_prodi }}
                </div>
</td>
        </tr>
    </table>

</body>

</html>