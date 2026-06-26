<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan SK 6</title>

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
            margin-top: 0cm;
            width: 100%;
            height: auto;
            display: block;
        }

        .text-left {
            text-align: left;
            margin-left: 30px;
        }

        .kop {
            margin-top: 0cm;
            margin-left: -1.5cm;
            margin-right: -1.5cm;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    <!-- 1. SURAT KETERANGAN LULUS MATA KULIAH -->
    <div class="kop">
        <img src="{{ $kopBase64 }}" alt="Kop Surat">
    </div>

    <div class="text-center">
        <div class="judul">SURAT KETERANGAN LULUS MATA KULIAH</div>
        <div class="nomor">
            Nomor: {{ $nomor_surat_sklmk }}
        </div>
    </div>

    <div class="paragraf">
        Yang bertanda tangan di bawah ini kami, Ketua Program Studi {{ $prodi }} Fakultas {{ $fakultas  }}
        Universitas Islam Internasional Darullughah Wadda’wah Bangil Pasuruan menerangkan
        dengan sesungguhnya bahwa:
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
            <td>{{ $fakultas }} / {{ $prodi }}</td>
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
        Mahasiswa tersebut benar-benar aktif dan telah memenuhi segala persyaratan akademik
        untuk mengikuti ujian skripsi antara lain ;
    </div>

    <div class="text-left">
        1. Telah lulus semua mata kuliah;<br>
        2. Telah memberikan sumbangan wakaf buku untuk perpustakaan Dalwa.
    </div>

    <div class="paragraf">
        Adapun bukti-bukti dan keterangan tentang hal yang tersebut diatas sebagaimana terlampir,
        dan apabila dikemudian hari terdapat kesalahan pada catatan kami dan yang bersangkutan
        terbukti tidak sesuai dengan ketentuan diatas maka surat keterangan ini bisa dicabut kembali.
    </div>

    <div class="paragraf">
        Demikian surat keterangan ini kami buat dengan sebenar-benarnya sebagai prasarat agar
        mahasiswa tersebut bisa mengikuti ujian skripsi.
    </div>

    <table class="ttd" width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;">
        <tr>
            <td width="55%"></td>
            <td width="45%" style="text-align:center;">
                Bangil, {{ $tanggal_surat }}<br>
                Ketua Program Studi,<br>
                <br><br><br>
                ( {{ $kaprodi_nama }} )
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    <!-- 2. SURAT KETERANGAN ADMINISTRASI KEUANGAN -->
    <div class="kop">
        <img src="{{ $kopBase64 }}" alt="Kop Surat">
    </div>

    <div class="text-center">
        <div class="judul">SURAT KETERANGAN ADMINISTRASI KEUANGAN</div>
        <div class="nomor">
            Nomor: {{ $nomor_surat_skak }}
        </div>
    </div>

    <div class="paragraf">
        Yang bertanda tangan di bawah ini kami, Kepala Biro Administrasi Keuangan
        Universitas Islam Internasional Darullughah Wadda’wah Bangil Pasuruan menerangkan
        dengan sesungguhnya bahwa:
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
            <td>{{ $fakultas }} / {{ $prodi }}</td>
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
        Mahasiswa tersebut benar-benar aktif dan telah memenuhi segala persyaratan administrasi umum
        (Melunasi keuangan) antara lain:
    </div>

    <div class="text-left">
        1. Telah melunasi Uang Pendaftaran;<br>
        2. Telah melunasi SPP sampai semester terakhir;<br>
        3. Telah melunasi biaya KKN dan PPL;<br>
        4. Telah melunasi biaya ujian skripsi.
    </div>

    <div class="paragraf">
        Adapun bukti-bukti dan keterangan tentang hal yang tersebut diatas sebagaimana terlampir,
        dan apabila dikemudian hari terdapat kesalahan pada catatan kami dan yang bersangkutan
        terbukti tidak sesuai dengan ketentuan diatas maka surat keterangan ini bisa dicabut kembali.
    </div>

    <div class="paragraf">
        Demikian surat keterangan ini kami buat dengan sebenar-benarnya sebagai prasarat agar
        mahasiswa tersebut bisa mengikuti ujian skripsi.
    </div>

    <table class="ttd" width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;">
        <tr>
            <td width="55%"></td>
            <td width="45%" style="text-align:center;">
                Bangil, {{ $tanggal_surat }}<br>
                Kepala Biro Administrasi Keuangan,<br>
                <br><br><br>
                {{ $skak_nama }}
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    <!-- 3. SURAT KETERANGAN TASMA, KKN, DAN PPL -->
    <div class="kop">
        <img src="{{ $kopBase64 }}" alt="Kop Surat">
    </div>

    <div class="text-center">
        <div class="judul">SURAT KETERANGAN TASMA, KKN, DAN PPL</div>
        <div class="nomor">
            Nomor: {{ $nomor_surat_sktkp }}
        </div>
    </div>

    <div class="paragraf">
        Yang bertanda tangan di bawah ini kami, Ketua LPkM Universitas Islam Internasional
        Darullughah Wadda’wah Bangil Pasuruan menerangkan dengan sesungguhnya bahwa:
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
            <td>{{ $fakultas }} / {{ $prodi }}</td>
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
        Mahasiswa tersebut benar-benar aktif dan telah memenuhi segala persyaratan akademik
        untuk mengikuti ujian skripsi antara lain ;
    </div>

    <div class="text-left">
        3. Telah dinyatakan lulus sebagai peserta Tasma.<br>
        4. Telah dinyatakan lulus sebagai peserta KKN.<br>
        5. Telah dinyatakan lulus sebagai peserta PPL.
    </div>

    <div class="paragraf">
        Adapun bukti-bukti dan keterangan tentang hal yang tersebut diatas sebagaimana terlampir,
        dan apabila dikemudian hari terdapat kesalahan pada catatan kami dan yang bersangkutan
        terbukti tidak sesuai dengan ketentuan diatas maka surat keterangan ini bisa dicabut kembali.
    </div>

    <div class="paragraf">
        Demikian surat keterangan ini kami buat dengan sebenar-benarnya sebagai prasarat agar
        mahasiswa tersebut bisa mengikuti ujian skripsi.
    </div>

    <table class="ttd" width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;">
        <tr>
            <td width="55%"></td>
            <td width="45%" style="text-align:center;">
                Bangil, {{ $tanggal_surat }}<br>
                Ketua TASMA, KKN & PPL<br>
                <br><br><br>
                {{ $sktkp_nama }}
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    <!-- 4. SURAT KETERANGAN UJIAN KOMPREHENSIF DINIYAH -->
    <div class="kop">
        <img src="{{ $kopBase64 }}" alt="Kop Surat">
    </div>

    <div class="text-center">
        <div class="judul">SURAT KETERANGAN UJIAN KOMPREHENSIF DINIYAH</div>
        <div class="nomor">
            Nomor: {{ $nomor_surat_skukd }}
        </div>
    </div>

    <div class="paragraf">
        Yang bertanda tangan di bawah ini kami, Wakil Rektor I Bidang Akademik Universitas Islam Internasional
        Darullughah Wadda’wah Bangil Pasuruan menerangkan dengan sesungguhnya bahwa:
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
            <td>{{ $fakultas }} / {{ $prodi }}</td>
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
        Mahasiswa tersebut benar-benar aktif dan telah memenuhi segala persyaratan akademik
        untuk mengikuti ujian skripsi antara lain ;
    </div>

    <div class="text-left">
        1. Telah lulus ujian komprehensif diniyah.
    </div>

    <div class="paragraf">
        Adapun bukti-bukti dan keterangan tentang hal yang tersebut diatas sebagaimana terlampir,
        dan apabila dikemudian hari terdapat kesalahan pada catatan kami dan yang bersangkutan
        terbukti tidak sesuai dengan ketentuan diatas maka surat keterangan ini bisa dicabut kembali.
    </div>

    <div class="paragraf">
        Demikian surat keterangan ini kami buat dengan sebenar-benarnya sebagai prasarat agar
        mahasiswa tersebut bisa mengikuti ujian skripsi.
    </div>

    <table class="ttd" width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;">
        <tr>
            <td width="55%"></td>
            <td width="45%" style="text-align:center;">
                Bangil, {{ $tanggal_surat }}<br>
                Wakil Rektor I,<br>
                <br><br><br>
                {{ $skukd_nama }}
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    <!-- 5. SURAT KETERANGAN QISMUL AMAN -->
    <div class="kop">
        <img src="{{ $kopBase64 }}" alt="Kop Surat">
    </div>

    <div class="text-center">
        <div class="judul" style="text-decoration: underline;">SURAT KETERANGAN QISMUL AMAN</div>
        <div class="nomor" style="font-weight: bold;">
            Nomor: {{ $nomor_surat_skqa }}
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
            <td>{{ $fakultas }} / {{ $prodi }}</td>
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
        Surat keterangan ini berlaku sejak tanggal {{ $tanggal_awal }} sampai tanggal {{ $tanggal_akhir }} apabila dikemudian hari terdapat kesalahan pada
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
                Bangil, {{ $tanggal_surat }}<br>
                Ketua Qismul Aman<br>
                <br><br><br>
                {{ $skqa_nama }}
            </td>
        </tr>
    </table>

</body>

</html>