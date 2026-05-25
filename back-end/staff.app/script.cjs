const fs = require('fs');

const configs = {
    'SuratIzinPenelitianController.php': {
        varName: '$sip',
        formatCall: "$formattedNoSurat = \\App\\Services\\SuratService::formatNomorSurat('SIP', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id'] ?? null);\n            $sip->nomor = $formattedNoSurat;",
        validatorInjection: "                'no_surat' => 'required|string|max:255',\n"
    },
    'SuratKeteranganAdministrasiKeuanganController.php': {
        varName: '$data',
        formatCall: "$noSurat = \\App\\Services\\SuratService::formatNomorSurat('SKAK', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id'] ?? null);\n            $data->nomor_surat = $noSurat;",
        validatorInjection: "                'no_surat' => 'required|string|max:255',\n"
    },
    'SuratKeteranganAktifMahasiswaController.php': {
        varName: '$data',
        formatCall: "$noSurat = \\App\\Services\\SuratService::formatNomorSurat('SKAM', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id'] ?? null);\n            $data->nomor_surat = $noSurat;",
        validatorInjection: "                'no_surat' => 'required|string|max:255',\n"
    },
    'SuratKeteranganController.php': {
        varName: '$sk',
        formatCall: "$formattedNoSurat = \\App\\Services\\SuratService::formatNomorSurat('SK', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id'] ?? null);\n            $sk->nomor_surat = $formattedNoSurat;",
        validatorInjection: "                'no_surat' => 'required|string|max:255',\n"
    },
    'SuratKeteranganKknController.php': {
        varName: '$skk',
        formatCall: "$noSurat = \\App\\Services\\SuratService::NoSuratKeteranganTasmaKknPpl($validate['no_surat']);\n            $skk->nomor_surat = $noSurat;",
        validatorInjection: "                'no_surat' => 'required|string|max:255',\n"
    },
    'SuratKeteranganLulusMataKuliahController.php': {
        varName: '$sklmk',
        formatCall: "$noSurat = \\App\\Services\\SuratService::formatNomorSurat('SKLM', $validate['no_surat'], $validate['tanggal'] ?? date('Y-m-d'), $validate['prodi_id'] ?? null);\n            $sklmk->nomor_surat = $noSurat;",
        validatorInjection: "                'no_surat' => 'required|string|max:255',\n"
    },
    'SuratKeteranganPplController.php': {
        varName: '$skp',
        formatCall: "$noSurat = \\App\\Services\\SuratService::NoSuratKeteranganTasmaKknPpl($validate['no_surat']);\n            $skp->nomor_surat = $noSurat;",
        validatorInjection: "                'no_surat' => 'required|string|max:255',\n"
    },
    'SuratKeteranganTasmaKknPplController.php': {
        varName: '$data',
        formatCall: "$noSurat = \\App\\Services\\SuratService::formatNomorSurat('STTKP', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id'] ?? null);\n            $data->nomor_surat = $noSurat;",
        validatorInjection: "                'no_surat' => 'required|string|max:255',\n"
    },
    'SuratKeteranganUjianKomprehensifDiniyahController.php': {
        varName: '$skukd',
        formatCall: "$noSurat = \\App\\Services\\SuratService::formatNomorSurat('SKUKD', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id'] ?? null);\n            $skukd->nomor_surat = $noSurat;",
        validatorInjection: "                'no_surat' => 'required|string|max:255',\n"
    },
    'SuratPernyataanVerifikasiNilaiController.php': {
        varName: '$data',
        formatCall: "$noSurat = \\App\\Services\\SuratService::formatNomorSurat('SPMVN', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id'] ?? null);\n            $data->nomor_surat = $noSurat;",
        validatorInjection: "                'no_surat' => 'required|string|max:255',\n"
    },
    'SuratTugasController.php': {
        varName: '$st',
        formatCall: "$formattedNoSurat = \\App\\Services\\SuratService::formatNomorSurat('ST', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id'] ?? null);\n            $st->nomor = $formattedNoSurat;",
        validatorInjection: "                'no_surat' => 'required|string|max:255',\n"
    }
};

for (const c in configs) {
    const filePath = 'c:/laragon/www/staff/back-end/staff.app/app/Http/Controllers/Api/' + c;
    if (fs.existsSync(filePath)) {
        let content = fs.readFileSync(filePath, 'utf8');
        const updateRegex = /public function update\s*\([^)]+\)\s*\{([\s\S]*?\$validate\s*=\s*\$validator->validated\(\);)/;
        
        const match = content.match(updateRegex);
        if (match) {
            let updateBlock = match[0];
            
            if (!updateBlock.includes("'no_surat'")) {
                const validatorTarget = /Validator::make\(\$request->all\(\),\s*\[\s*/;
                updateBlock = updateBlock.replace(validatorTarget, `Validator::make($request->all(), [\n${configs[c].validatorInjection}`);
                
                updateBlock = updateBlock + `\n\n            ${configs[c].formatCall}\n`;
                
                content = content.replace(match[0], updateBlock);
                fs.writeFileSync(filePath, content, 'utf8');
                console.log('Injected update() in ' + c);
            } else {
                console.log('Already has no_surat in ' + c);
            }
        } else {
            console.log('Could not find update method or $validate in ' + c);
        }
        
        // Fix prodi_mahasiswa bug in SuratKeteranganKknController
        if (c === 'SuratKeteranganKknController.php' && content.includes('$data->prodi_mahasiswa')) {
            content = content.replace(/\$data->prodi_mahasiswa/g, '$data->prodi_mhs');
            fs.writeFileSync(filePath, content, 'utf8');
            console.log('Fixed prodi_mahasiswa in ' + c);
        }
    }
}
