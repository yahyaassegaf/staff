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
            margin-top: -0.1cm;
            margin-left: -1cm;
            margin-right: -1cm;
        }

        .kop img {
            width: 100%;
            max-height: 155px;
            object-fit: contain;
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

        .margin-bottom-10 {
            margin-bottom: 10px;
        }

        .margin-bottom-20 {
            margin-bottom: 20px;
        }

        .margin-top-20 {
            margin-top: 20px;
        }

        /* ===== TABLE BORDERED ===== */
        .table-bordered {
            border: 1px solid #000;
            margin-bottom: 15px;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #000;
            padding: 8px;
        }

        .table-bordered th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        /* ===== PEMBAHASAN ===== */
        .pembahasan-content {
            text-align: justify;
            line-height: 1.6;
            margin-top: 10px;
            padding: 10px;
            background-color: #fafafa;
            border: 1px solid #ddd;
        }

        .section-title {
            font-weight: bold;
            font-size: 12pt;
            margin-top: 20px;
            margin-bottom: 10px;
            text-decoration: underline;
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

        <div class="text-center text-bold text-underline" style="font-size: 14pt;">
            NOTULA RAPAT
        </div>
        <div class="text-center margin-bottom-20">
            Nomor: {{ $nomor_surat }}
        </div>

        <!-- DATA RAPAT -->
        <table class="margin-bottom-20">
            <tr>
                <td width="25%">Agenda</td>
                <td width="3%">:</td>
                <td><strong>{{ $agenda }}</strong></td>
            </tr>
            <tr>
                <td>Hari/Tanggal</td>
                <td>:</td>
                <td>{{ $tanggal }}</td>
            </tr>
            <tr>
                <td>Waktu</td>
                <td>:</td>
                <td>{{ $waktu ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tempat</td>
                <td>:</td>
                <td>{{ $tempat ?? '-' }}</td>
            </tr>
        </table>

        <!-- HASIL PEMBAHASAN -->
        <div class="section-title">HASIL PEMBAHASAN</div>

        <div class="pembahasan-content">
            @if(isset($pembahasan) && $pembahasan)
            {!! $pembahasan !!}
            @else
            <em>Tidak ada hasil pembahasan.</em>
            @endif
        </div>

        <!-- DAFTAR ANGGOTA RAPAT -->
        <div class="section-title">DAFTAR HADIR PESERTA RAPAT</div>

        @if(isset($anggota) && count($anggota) > 0)
        <table class="table-bordered">
            <thead>
                <tr>
                    <th width="8%">No</th>
                    <th>Nama</th>
                </tr>
            </thead>
            <tbody>
                @foreach($anggota as $index => $a)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $a->user->name ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p><em>Tidak ada data anggota rapat.</em></p>
        @endif

        <!-- PENUTUP -->
        <p class="margin-top-20 text-justify">
            Demikian notula rapat ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
        </p>

    </div>

</body>

</html>
