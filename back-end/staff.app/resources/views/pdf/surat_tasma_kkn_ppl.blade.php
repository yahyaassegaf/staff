<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan TASMA, KKN, dan PPL</title>

    <style>
        /* ===== PAGE ===== */
        @page {
            size: A4;
            margin: 0;
            /* wajib nol agar kop full */
        }

        .ol-ikut-table {
            margin-left: var(--indent-surat);
            /* SAMA PERSIS */
            padding-left: 0;
            list-style-position: outside;
        }

        .alenia {
            text-indent: 0.5cm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
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
            /* penuh kiri kanan */
            height: auto;
            display: block;
            /* hilangkan whitespace */
        }

        .table-indent {
            margin-left: 1cm;
            /* sejajar dengan angka ol (3,4,5) */
        }


        /* ===== KONTEN SURAT ===== */
        .content {
            padding: 0cm 2cm 3cm 2cm;
            /* margin asli surat */
        }

        .text-center {
            text-align: center;
        }

        .text-justify {
            text-align: justify;
        }

        .text-right {
            text-align: right;
        }

        .title {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 5px;
        }

        .subtitle {
            margin-top: 2px;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table td {
            vertical-align: top;
            padding: 3px 0;
        }

        .data-table td.label {
            width: 30%;
        }

        .data-table td.separator {
            width: 5%;
            text-align: center;
        }

        .signature {
            margin-top: 20px;
            width: 100%;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <!-- ===== KOP FULL WIDTH ===== -->
    <div class="kop">
        <img src="{{ $kopBase64 }}" alt="Kop Surat">
    </div>

    <!-- ===== ISI SURAT ===== -->
    <div class="content">
        <div class="text-center title">
            SURAT KETERANGAN TASMA, KKN, DAN PPL
        </div>

        <div class="text-center subtitle">
            Nomor: {{ $nomor_surat }}
        </div>

        <p class="text-justify alenia">
            Yang bertanda tangan di bawah ini, kami Ketua LPKM Universitas Islam Internasional
            Darullughah Wadda’wah Bangil Pasuruan menerangkan dengan sesungguhnya bahwa:
        </p>

        <!-- DATA MAHASISWA -->
        <table class="data-table table-indent">
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="separator">:</td>
                <td>{{ $nama }}</td>
            </tr>
            <tr>
                <td class="label">Tempat, Tgl. Lahir</td>
                <td class="separator">:</td>
                <td>{{ $tempat_lahir }},{{ $tanggal_lahir }}</td>
            </tr>
            <tr>
                <td class="label">NIM</td>
                <td class="separator">:</td>
                <td>{{ $nim }}</td>
            </tr>
            <tr>
                <td class="label">Fakultas / Prodi</td>
                <td class="separator">:</td>
                <td>{{ $fakultas }} / {{ $prodi }}</td>
            </tr>
            <tr>
                <td class="label">Alamat Rumah</td>
                <td class="separator">:</td>
                <td>{{ $alamat }}</td>
            </tr>
            <tr>
                <td class="label">Kelas</td>
                <td class="separator">:</td>
                <td>{{ $kelas }}</td>
            </tr>
        </table>

        <p class="text-justify alenia">
            Mahasiswa tersebut benar-benar aktif, dan telah memenuhi segala persyaratan akademik
            untuk mengikuti ujian skripsi antara lain:
        </p>

        <!-- <ol start="1" class="ol-ikut-table">
            <li>Telah dinyatakan lulus sebagai peserta Tasma.</li>
            <li>Telah dinyatakan lulus sebagai peserta KKN.</li>
            <li>Telah dinyatakan lulus sebagai peserta PPL.</li>
        </ol> -->
        <table class="data-table table-indent">
            <tr>
                <td width="5%">1.</td>
                <td>Telah dinyatakan lulus sebagai peserta Tasma.</td>
            </tr>
            <tr>
                <td>2.</td>
                <td>Telah dinyatakan lulus sebagai peserta KKN.</td>
            </tr>
            <tr>
                <td>3.</td>
                <td>Telah dinyatakan lulus sebagai peserta PPL.</td>
            </tr>
        </table>


        <p class="text-justify alenia">
            Adapun bukti-bukti dan keterangan tentang hal yang tersebut di atas, sebagaimana
            terlampir, dan apabila dikemudian hari terdapat kesalahan pada catatan kami dan yang
            bersangkutan terbukti tidak sesuai dengan ketentuan di atas, maka surat keterangan ini
            bisa dicabut kembali.
        </p>

        <p class="text-justify alenia">
            Demikian surat keterangan ini kami buat dengan sebenar-benarnya sebagai prasyarat agar
            mahasiswa tersebut bisa mengikuti ujian skripsi.
        </p>

        <!-- TANDA TANGAN -->
        <table class="signature">
            <tr>
                <td width="60%"></td>
                <td class="text-center">
                    Bangil, {{ $tanggal }}<br>
                    Ketua TASMA, KKN & PPL
                    <br><br><br><br>
                    <span class="signature-name">{{ $ketua }}</span>
                </td>
            </tr>
        </table>

    </div>

</body>

</html>