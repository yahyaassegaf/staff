<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Qismul Aman</title>

    <style>
        /* WAJIB untuk DomPDF */
        @page {
            margin: 0cm 2cm 1cm 2cm;
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
            margin-top: 0px;
            width: 100%;
            height: auto;
            display: block;
        }

        .text-left {
            text-align: left;
            margin-left: 30px;
        }

        .kop {
            margin-top: 0.1cm;
            margin-left: -1.5cm;
            margin-right: -1.5cm;
        }
    </style>
</head>

<body>

    <div class="kop">
        <img src="{{ $kopBase64 }}" alt="Kop Surat">
    </div>

    <div class="text-center">
        <div class="judul" style="text-decoration: underline;">SURAT KETERANGAN QISMUL AMAN</div>
        <div class="nomor" style="font-weight: bold;">
            Nomor: {{ $nomor_surat }}
        </div>
    </div>

    <div class="paragraf">
        Yang bertanda tangan di bawah ini kami, Ketua Qismul Aman Ma'had Darullughah
        Wadda’wah Bangil Pasuruan menerangkan dengan sesungguhnya bahwa:
    </div>

    <table class="data">
        <tr>
            <td class="label">Nama Lengkap</td>
            <td width="10">:</td>
            <td>{{ $nama }}</td>
        </tr>
        <tr>
            <td class="label">Tempat Tgl. Lahir</td>
            <td>:</td>
            <td>{{ $tempat_lahir }} , {{ $tanggal_lahir }}</td>
        </tr>
        <tr>
            <td class="label">NIM</td>
            <td>:</td>
            <td>{{ $nim }}</td>
        </tr>
        <tr>
            <td class="label">Fakultas/Prodi</td>
            <td>:</td>
            <td>{{ $prodi }}</td>
        </tr>
        <tr>
            <td class="label">Kelas Diniyah</td>
            <td>:</td>
            <td>{{ $kelas }}</td>
        </tr>
        <tr>
            <td class="label">Alamat Rumah</td>
            <td>:</td>
            <td>{{ $alamat }}</td>
        </tr>
    </table>

    <div class="paragraf" style="text-indent: 0;">
        Benar-benar santri aktif ma'had Darullughah Wadda'wah Bangil Jatim yang pada saat ini
        berdasarkan catatan kami mahasiswa tersebut ;
    </div>

    <div class="text-left">
        1. Tidak pernah melakukan pelanggaran berat<br>
        2. Tidak pernah melakukan hal-hal yang dapat merusak citra ma'had<br>
        3. Tidak dalam masa skorsing atau diberhentikan
    </div>

    <div class="paragraf">
        Surat keterangan ini berlaku selama 3 bulan terhitung sejak <b>tgl {{ $tanggal_berlaku_dari }}</b> sampai <b>tgl {{ $tanggal_berlaku_sampai }}</b> apabila dikemudian hari terdapat kesalahan pada
        catatan kami dan yang bersangkutan terbukti tidak sesuai dengan ketentuan di atas maka surat
        keterangan ini bisa dicabut kembali.
    </div>

    <div class="paragraf">
        Demikian surat keterangan ini kami buat dengan sebenar-benarnya sebagai prasarat
        agar mahasiswa tersebut bisa mengikuti ujian skripsi dan dapat dipergunakan sebagaimana
        semestinya.
    </div>

    <table class="ttd" width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;">
        <tr>
            <td width="50%"></td>
            <td width="50%" style="text-align:center;">
                Pasuruan, {{ $tanggal_surat }}<br>
                Ketua Qismul Aman<br>
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: -15px; margin-bottom: -20px;">
                    <tr>
                        <td style="height: 125px; text-align:center; vertical-align:middle; padding: 0;">
                            <div style="position: relative; width: 100%; height: 125px;">
                                @if(!empty($stempel))
                                <img src="{{ $stempel }}" style="position: absolute; left: 15px; top: 5px; width: 110px; height: 110px; z-index: 2;">
                                @endif

                                @if(!empty($ttd))
                                <img src="{{ $ttd }}" style="position: absolute; left: 25px; top: 15px; width: 200px; z-index: 1;">
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
                {{ $ketua }}
            </td>
        </tr>
    </table>

</body>

</html>