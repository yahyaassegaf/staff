<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\Batch;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Events\BeforeSheet;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MahasiswaImport implements ToCollection, WithHeadingRow, WithEvents, SkipsEmptyRows
{
    private $errors = [];
    private $successCount = 0;
    private $failedCount = 0;
    private $currentSheetName = null;
    private $sheetResolvedProdiId = null;
    private $batchId = null;
    private $skippedSheets = [];
    private $skipCurrentSheet = false;
    private $skippedNames = [];

    /**
     * Daftarkan event untuk menangkap nama sheet sebelum diproses
     */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                // Ambil judul sheet
                $this->currentSheetName = $event->sheet->getDelegate()->getTitle();

                // Coba resolve Prodi dari nama sheet (Alias dulu, baru Nama)
                $this->sheetResolvedProdiId = $this->resolveProdiId($this->currentSheetName);

                // Jika prodi tidak ditemukan dari nama sheet, skip sheet ini
                if (!$this->sheetResolvedProdiId) {
                    $this->skipCurrentSheet = true;
                    $this->skippedSheets[] = $this->currentSheetName;
                    return;
                }

                $this->skipCurrentSheet = false;

                // Buat batch baru hanya untuk sheet yang valid
                $batch = Batch::create([
                    'nama_batch' => $this->currentSheetName . ' - ' . date('Y-m-d H:i:s'),
                    'tanggal_import' => date('Y-m-d')
                ]);
                $this->batchId = $batch->id;
            },
        ];
    }

    public function collection(Collection $rows)
    {
        // Jika sheet ini ditandai untuk di-skip, langsung return
        if ($this->skipCurrentSheet) {
            return;
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +1 heading, +1 1-based index

            try {
                // Bersihkan input
                $nama = trim($row['nama'] ?? '');
                $nim = trim($row['nim'] ?? '');
                $nik = trim($row['nik'] ?? '');
                $ttlRaw = trim($row['tempat_tanggal_lahir'] ?? $row['tgl_lahir'] ?? $row['tanggal_lahir'] ?? '');
                $nilaiAkreditasi = trim($row['nilai_akreditasi'] ?? '');
                $nomorSkBanPt = trim($row['nomor_sk_ban_pt'] ?? '');
                $nomorIjazahNasional = trim($row['nomor_ijazah_nasional'] ?? '');
                $tanggalSkYudisium = trim($row['tanggal_sk_yudisium'] ?? '');
                $tanggalPenerbitan = trim($row['tanggal_penerbitan'] ?? '');

                // Log beberapa baris pertama untuk debugging header/data
                if ($index < 3) {
                    Log::info("Import Mahasiswa - Sheet: {$this->currentSheetName}, Baris {$rowNumber}", [
                        'raw_keys' => $row->keys()->toArray(),
                        'nama' => $nama,
                        'nim' => $nim,
                        'nik' => $nik,
                    ]);
                }

                // Skip baris yang benar-benar kosong (semua kolom utama kosong)
                if ($nama === '' && $nim === '' && $nik === '') {
                    continue;
                }

                // Validasi semua kolom wajib — jika salah satu kosong, skip baris ini
                $requiredFields = [
                    'Nama' => $nama,
                    'NIM' => $nim,
                    'NIK' => $nik,
                    'Tempat/Tgl Lahir' => $ttlRaw,
                    'Nilai Akreditasi' => $nilaiAkreditasi,
                    'Nomor SK BAN-PT' => $nomorSkBanPt,
                    'Nomor Ijazah Nasional' => $nomorIjazahNasional,
                    'Tanggal SK Yudisium' => $tanggalSkYudisium,
                    'Tanggal Penerbitan' => $tanggalPenerbitan,
                ];

                $emptyFields = [];
                foreach ($requiredFields as $label => $value) {
                    if ($value === '' || $value === null) {
                        $emptyFields[] = $label;
                    }
                }

                if (!empty($emptyFields)) {
                    $identifier = $nama !== '' ? $nama : ($nim !== '' ? "NIM: $nim" : "Baris $rowNumber");
                    $kolomKosong = implode(', ', $emptyFields);
                    $this->skippedNames[] = "{$identifier} (Sheet: {$this->currentSheetName}, Kolom kosong: {$kolomKosong})";
                    continue;
                }

                // Resolve Prodi ID
                $prodiId = null;
                $prodiValue = null;

                // 1. Prioritas: Cek apakah nama sheet adalah identitas Prodi
                if ($this->sheetResolvedProdiId) {
                    $prodiId = $this->sheetResolvedProdiId;
                }
                // 2. Fallback: Cek kolom 'prodi', 'program_studi', atau 'prodi_id'
                else {
                    $prodiValue = $row['prodi_id'] ?? $row['prodi'] ?? $row['program_studi'] ?? null;
                    if ($prodiValue) {
                        $prodiId = $this->resolveProdiId((string) $prodiValue);
                    }
                }

                if (!$prodiId) {
                    $this->addError($rowNumber, "Program studi tidak ditemukan (Sheet: {$this->currentSheetName}, Input: " . ($prodiValue ?? 'N/A') . ")");
                    $this->failedCount++;
                    continue;
                }

                // Parsing tempat & tanggal lahir dari kolom gabungan
                $tempatLahir = $this->parseTempatLahir($ttlRaw);
                $tglLahir = $this->parseTglLahirString($ttlRaw);

                // Update or Create Mahasiswa
                Mahasiswa::updateOrCreate(
                    ['nim' => (string) $nim],
                    [
                        'nama' => $nama,
                        'nik' => (string) $nik,
                        'tempat_lahir' => $tempatLahir,
                        'tgl_lahir' => $tglLahir,
                        'nilai_akreditasi' => $nilaiAkreditasi,
                        'nomor_sk_ban_pt' => $nomorSkBanPt,
                        'nomor_ijazah_nasional' => $nomorIjazahNasional,
                        'tanggal_sk_yudisium' => $tanggalSkYudisium,
                        'tanggal_penerbitan' => $tanggalPenerbitan,
                        'prodi_id' => $prodiId,
                        'batch_id' => $this->batchId,
                        'status' => strtolower((string) ($row['status'] ?? 'belum')) === 'sudah' ? 'sudah' : 'belum',
                    ]
                );

                $this->successCount++;
            } catch (\Throwable $th) {
                $this->addError($rowNumber, $th->getMessage());
                $this->failedCount++;
            }
        }
    }

    /**
     * Mencari Prodi ID berdasarkan Alias atau Nama
     */
    protected function resolveProdiId(string $identifier): ?int
    {
        $identifier = trim($identifier);
        if (empty($identifier)) return null;

        // Jika identifier adalah angka, coba cari langsung sebagai ID
        if (is_numeric($identifier)) {
            $prodi = Prodi::find($identifier);
            if ($prodi) return (int) $prodi->id;
        }

        // Cari berdasarkan Alias (PBA, pba, dll)
        $prodi = Prodi::where('alias', $identifier)->first();
        if ($prodi) return (int) $prodi->id;

        // Cari berdasarkan Nama Lengkap (Pendidikan Bahasa Arab, dll)
        $prodi = Prodi::where('nama', $identifier)->first();
        if ($prodi) return (int) $prodi->id;

        // Cari berdasarkan Nama Lengkap (Case Insensitive / LIKE)
        $prodi = Prodi::where('nama', 'LIKE', $identifier)->first();
        return $prodi ? (int) $prodi->id : null;
    }

    /**
     * Parsing tempat lahir dari string gabungan "Tempat, Tanggal"
     */
    protected function parseTempatLahir($value): ?string
    {
        if (!$value) return null;
        if (strpos($value, ',') !== false) {
            return trim(explode(',', $value)[0]);
        }
        return null; // Asumsi jika tidak ada koma, itu hanya tanggal atau format lain
    }

    /**
     * Parsing tanggal lahir dari string gabungan "Tempat, Tanggal"
     * Menyimpan bagian tanggal apa adanya sebagai varchar (misal: "14 Maret 1997")
     */
    protected function parseTglLahirString($value): ?string
    {
        if (!$value) return null;

        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }

        $value = (string) $value;

        // Jika format "Tempat, Tanggal" → ambil bagian setelah koma
        if (strpos($value, ',') !== false) {
            return trim(explode(',', $value, 2)[1]);
        }

        // Jika format angka Excel → konversi ke tanggal
        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return $value;
            }
        }

        // Kembalikan string apa adanya (misal: "14 Maret 1997")
        return trim($value);
    }

    private function addError(int $row, string $message): void
    {
        $this->errors[] = [
            'row' => $row,
            'message' => $message
        ];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }
    public function getFailedCount(): int
    {
        return $this->failedCount;
    }
    public function getSkippedSheets(): array
    {
        return $this->skippedSheets;
    }
    public function getSkippedNames(): array
    {
        return $this->skippedNames;
    }
}
