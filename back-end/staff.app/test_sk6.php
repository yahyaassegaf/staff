<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

try {
    $id = \App\Models\SuratKeterangan6::first()->id;
    echo "SK6 ID: $id\n";
    $sk6 = \App\Models\SuratKeterangan6::with([
        'skLulusMk',
        'skAdminKeuangan',
        'skTasmaKknPpl',
        'skUjianKomprehensifDiniyah',
        'skQismulAman'
    ])->find($id);

    $sklmk = \App\Models\SuratKeteranganLulusMataKuliah::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_lulus_mata_kuliah.prodi_id')
        ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
        ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
        ->leftJoin('tanda_tangan as tt_prodi', 'tt_prodi.id', '=', 'prodi.tanda_tangan_id')
        ->select(
            'surat_keterangan_lulus_mata_kuliah.*',
            'prodi.nama as nama_prodi',
            'fakultas.nama as fakultas',
            'prodi.alias as alias_prodi',
            'prodi.nama_kepala as nama_kepala_prodi',
            'tt_prodi.nama as kaprodi_nama',
            'tt_prodi.gambar as kaprodi_gambar',
            'tt_prodi.tdd as kaprodi_tdd'
        )
        ->where('surat_keterangan_lulus_mata_kuliah.id', $sk6->surat_keterangan_lulus_mata_kuliah_id)
        ->first();
        
    echo "SKLMK found: " . ($sklmk ? 'Yes' : 'No') . "\n";
    
    $skak = $sk6->skAdminKeuangan;
    $sktkp = $sk6->skTasmaKknPpl;
    $skukd = $sk6->skUjianKomprehensifDiniyah;
    $skqa = $sk6->skQismulAman;

    $kopPath = base_path('../public_html/img/kop.jpg');
    $kopBase64 = \App\Services\SuratService::getBase64Image($kopPath);

    $kaprodiTtdBase64 = '';
    if ($sklmk->kaprodi_gambar) {
        $kaprodiTtdBase64 = \App\Services\SuratService::getBase64Image(base_path('../public_html/' . $sklmk->kaprodi_gambar));
    } elseif ($sklmk->kaprodi_tdd) {
        $kaprodiTtdBase64 = $sklmk->kaprodi_tdd;
    }
    echo "Kaprodi TTd Base64 length: " . strlen($kaprodiTtdBase64) . "\n";

    $getTtdJabatan = function ($key) {
        $setting = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', $key)->first();
        if ($setting && $setting->tandaTangan) {
            $ttdBase64 = '';
            if ($setting->tandaTangan->gambar) {
                $ttdBase64 = \App\Services\SuratService::getBase64Image(base_path('../public_html/' . $setting->tandaTangan->gambar));
            } elseif ($setting->tandaTangan->tdd) {
                $ttdBase64 = $setting->tandaTangan->tdd;
            }
            return [
                'nama' => $setting->tandaTangan->nama,
                'ttd' => $ttdBase64,
                'nama_jabatan' => $setting->nama_jabatan,
            ];
        }
        return ['nama' => '', 'ttd' => '', 'nama_jabatan' => ''];
    };

    $ttdSkak = $getTtdJabatan('kepala_biro_keuangan');
    $ttdSktkp = $getTtdJabatan('ketua_tasma');
    $ttdSkukd = $getTtdJabatan('ketua_komprehensif');
    $ttdSkqa = $getTtdJabatan('ketua_qismul_aman');
    echo "Ttd fetched successfully.\n";

    $pdfData = [
        'nomor_surat_sklmk' => $sklmk->nomor_surat,
        'nomor_surat_skak' => $skak ? $skak->nomor_surat : '-',
        'nomor_surat_sktkp' => $sktkp ? $sktkp->nomor_surat : '-',
        'nomor_surat_skukd' => $skukd ? $skukd->nomor_surat : '-',
        'nomor_surat_skqa' => $skqa ? $skqa->nomor_surat : '-',
        
        'nama' => $sklmk->nama_lengkap,
        'tempat_lahir' => $sklmk->tempat_lahir,
        'tanggal_lahir' => \Carbon\Carbon::parse($sklmk->tanggal_lahir)->locale('id')->translatedFormat('d F Y'),
        'nim' => $sklmk->nim,
        'fakultas' => $sklmk->fakultas,
        'prodi' => $sklmk->nama_prodi,
        'alamat' => $sklmk->alamat_rumah,
        'kelas' => $sklmk->kelas_pondok,
        'alias_prodi' => $sklmk->alias_prodi,
        'nama_kepala_prodi' => $sklmk->nama_kepala_prodi,
        'tanggal' => $sklmk->tanggal,
        'tanggal_surat' => \Carbon\Carbon::parse($sklmk->tanggal)->locale('id')->translatedFormat('d F Y'),
        'kopBase64' => $kopBase64,
        'tanggal_awal' => $skqa && $skqa->tanggal_berlaku_dari ? \Carbon\Carbon::parse($skqa->tanggal_berlaku_dari)->locale('id')->translatedFormat('d F Y') : '-',
        'tanggal_akhir' => $skqa && $skqa->tanggal_berlaku_sampai ? \Carbon\Carbon::parse($skqa->tanggal_berlaku_sampai)->locale('id')->translatedFormat('d F Y') : '-',
        
        // Signatures
        'kaprodi_nama' => $sklmk->kaprodi_nama ?: $sklmk->nama_kepala_prodi,
        'kaprodi_ttd' => $kaprodiTtdBase64,
        
        'skak_nama' => $ttdSkak['nama'] ?: 'Dr. Musleh Harry, SH, M.Hum',
        'skak_ttd' => $ttdSkak['ttd'],
        
        'sktkp_nama' => $ttdSktkp['nama'] ?: 'Achmad Djuaini, M,Pd',
        'sktkp_ttd' => $ttdSktkp['ttd'],
        
        'skukd_nama' => $ttdSkukd['nama'] ?: 'Dr. Habib Zainal Abidin Bilfaqih, M.Pd.',
        'skukd_ttd' => $ttdSkukd['ttd'],
        
        'skqa_nama' => $ttdSkqa['nama'] ?: 'Ust. Fathul Munif',
        'skqa_ttd' => $ttdSkqa['ttd'],
    ];

    echo "PDF data built successfully.\n";
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sk_6', $pdfData)->setPaper('a4', 'portrait');
    $fileName = 'SK6_' . $sklmk->nim . '.pdf';
    $directory = base_path('../public_html/pdf/');

    if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
    }

    $path = $directory . $fileName;
    $pdf->save($path);
    echo "PDF saved successfully to $path\n";

    $fileName = basename($path);
    if (!\Illuminate\Support\Facades\Storage::disk('google')->exists($sklmk->nama_prodi . '/Surat Keterangan Lulus Mata Kuliah/' . $fileName)) {
        echo "Google drive exists returned false\n";
    }

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
