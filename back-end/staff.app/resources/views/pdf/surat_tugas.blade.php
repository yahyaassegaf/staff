<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <style>
        /* ===== PAGE ===== */
        @page {
            margin: 0cm 2cm 1cm 2cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
        }

        /* ===== KOP SURAT ===== */
        .kop {
            margin-top: 0.1cm;
            margin-left: -1.5cm;
            margin-right: -1.5cm;
        }

        .kop img {
            margin-top: 0px;
            width: 100%;
            height: auto;
            display: block;
        }

        /* ===== KONTEN ===== */
        .content {
            padding-bottom: 1cm;
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

        .text-italic {
            font-style: italic;
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

        .margin-bottom-10 {
            margin-bottom: 10px;
        }

        .table-list td {
            padding: 3px 2px;
        }
    </style>
    @php
        $fontFile = base_path('../public_html/fonts/Amiri-Regular.ttf');
        $fontPath = file_exists($fontFile) ? str_replace('\\', '/', realpath($fontFile)) : null;
    @endphp
    @if ($fontPath)
        <style>
            @font-face {
                font-family: 'Amiri';
                font-style: normal;
                font-weight: normal;
                src: url('{{ $fontPath }}') format('truetype');
            }

            @font-face {
                font-family: 'Amiri';
                font-style: normal;
                font-weight: bold;
                src: url('{{ $fontPath }}') format('truetype');
            }

            @font-face {
                font-family: 'Amiri';
                font-style: italic;
                font-weight: normal;
                src: url('{{ $fontPath }}') format('truetype');
            }

            @font-face {
                font-family: 'Amiri';
                font-style: italic;
                font-weight: bold;
                src: url('{{ $fontPath }}') format('truetype');
            }
        </style>
    @endif
</head>

<body>

    <!-- KOP SURAT -->
    <div class="kop">
        @if (isset($kopBase64))
            <img src="{{ $kopBase64 }}" alt="Kop Surat">
        @endif
    </div>

    <!-- ISI SURAT -->
    <div class="content">

        <div class="text-center text-bold text-underline" style="letter-spacing: 5px; font-size: 14pt;">
            SURAT TUGAS
        </div>
        <div class="text-center margin-bottom-20">
            Nomor : {{ $nomor }}
        </div>

        <table class="table-list">
            <tr>
                <td width="4%">1.</td>
                <td width="36%">Lembaga yang memberi tugas</td>
                <td width="3%">:</td>
                <td width="57%">{{ $lembaga_pemberi_tugas ?? '-' }}</td>
            </tr>
            <tr>
                <td>2.</td>
                <td>Yang diberi tugas</td>
                <td>:</td>
                <td></td>
            </tr>

            <!-- DATA DOSEN -->
            @if (isset($dosens) && count($dosens) > 0)
                @foreach ($dosens as $index => $dosen)
                    <tr>
                        <td></td>
                        <td>{{ chr(97 + $index) }}. Nama</td>
                        <td>:</td>
                        <td class="text-bold">{{ $dosen['nama'] }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td style="padding-left: 15px;">Alamat</td>
                        <td>:</td>
                        <td>{{ $dosen['alamat'] }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td style="padding-left: 15px;">Tugas</td>
                        <td>:</td>
                        <td>{{ $dosen['tugas'] }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td></td>
                    <td>a. Nama</td>
                    <td>:</td>
                    <td class="text-bold">{{ $nama_dosen ?? '' }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td style="padding-left: 15px;">Alamat</td>
                    <td>:</td>
                    <td>{{ $alamat_dosen ?? '' }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td style="padding-left: 15px;">Tugas</td>
                    <td>:</td>
                    <td>{{ $tugas_dosen ?? '' }}</td>
                </tr>
            @endif

            <tr>
                <td>3.</td>
                <td>Diberi Tugas untuk</td>
                <td>:</td>
                <td>{{ $tugasnya ?? 'Membimbing Skripsi' }}</td>
            </tr>

            <!-- DATA MAHASISWA -->
            <tr>
                <td></td>
                <td>a. Nama</td>
                <td>:</td>
                <td class="text-bold">{{ $nama_mhs }}</td>
            </tr>
            <tr>
                <td></td>
                <td>b. NIM / NIMKO</td>
                <td>:</td>
                <td>{{ $nim_nik }}</td>
            </tr>
            <tr>
                <td></td>
                <td>c. Jurusan / Prodi</td>
                <td>:</td>
                <td>{{ $fakultas_prodi }}</td>
            </tr>
            <tr>
                <td></td>
                <td style="vertical-align: top;">d. Judul Skripsi</td>
                <td style="vertical-align: top;">:</td>
                <td class="text-bold"
                    style="vertical-align: top; font-family: 'DejaVu Sans', sans-serif;">
                    "{{ $judul_skripsi }}"</td>
            </tr>

            <tr>
                <td>4.</td>
                <td>Masa penugasan</td>
                <td>:</td>
                <td>{{ $masa_penugasan }} s/d selesai</td>
            </tr>
            <tr>
                <td>5.</td>
                <td>Keterangan lain-lain</td>
                <td>:</td>
                <td>{{ $keterangan_lain ?? 'Harap dilaksanakan dengan penuh tanggung jawab.' }}</td>
            </tr>
        </table>

        <!-- TANDA TANGAN -->
        <table class="ttd" width="100%" cellpadding="0" cellspacing="0" style="margin-top:50px;">
            <tr>
                <td width="55%"></td>
                <td width="45%" class="text-center">
                    Pasuruan, {{ $tanggal ?? '' }}<br>
                    Dekan Fakultas {{ $fakultas ?? '' }}
                    <table width="100%" cellpadding="0" cellspacing="0"
                        style="margin-top: -20px; margin-bottom: -45px;">
                        <tr>
                            <td style="height: 155px; text-align:center; vertical-align:middle; padding:0;">
                                <div style="position: relative; width: 100%; height: 155px;">
                                    @if (!empty($stempel))
                                        <img src="{{ $stempel }}"
                                            style="position: absolute; left: -10px; top: 5px; width: 115px; height: 115px; z-index: 2;">
                                    @endif

                                    @if (!empty($ttd))
                                        <img src="{{ $ttd }}"
                                            style="position: absolute; left: 20px; top: 20px; width: 200px; z-index: 1;">
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class="text-bold text-underline">
                        {{ $nama_kepala ?? '' }}
                    </div>
                    NIDN. {{ $nidn_kepala ?? '' }}
                </td>
            </tr>
        </table>

    </div>

</body>

</html>
