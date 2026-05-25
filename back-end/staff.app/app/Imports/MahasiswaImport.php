<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\Batch;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Illuminate\Support\Str;

class MahasiswaImport implements ToCollection, WithHeadingRow, WithEvents
{
    private $errors = [];
    private $successCount = 0;
    private $failedCount = 0;
    private $currentSheetName = null;
    private $sheetResolvedProdiId = null;
    private $batchId = null;
    private $skippedSheets = [];
    private $skipCurrentSheet = false;

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

                // Lewati jika nama dan nim kosong (abaikan spasi)
                if ($nama === '' && $nim === '') {
                    continue;
                }

                $prodiId = null;

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
                    // Jika baris ada isinya (nama/nim) tapi prodi tidak ketemu, baru anggap error
                    $this->addError($rowNumber, "Program studi tidak ditemukan (Sheet: {$this->currentSheetName}, Input: " . ($prodiValue ?? 'N/A') . ")");
                    $this->failedCount++;
                    continue;
                }

                // Update or Create Mahasiswa
                Mahasiswa::updateOrCreate(
                    ['nim' => (string) ($row['nim'] ?? '')],
                    [
                        'nama' => (string) ($row['nama'] ?? ''),
                        'nik' => (string) ($row['nik'] ?? ''), // Pake string krn NIK panjang
                        'tgl_lahir' => $this->parseFileDate($row['tempat_tanggal_lahir'] ?? $row['tgl_lahir'] ?? $row['tanggal_lahir'] ?? null),
                        'nilai_akreditasi' => (string) ($row['nilai_akreditasi'] ?? ''),
                        'nomor_sk_ban_pt' => (string) ($row['nomor_sk_ban_pt'] ?? ''),
                        'nomor_ijazah_nasional' => (string) ($row['nomor_ijazah_nasional'] ?? ''),
                        'tanggal_sk_yudisium' => (string) ($row['tanggal_sk_yudisium'] ?? ''),
                        'tanggal_penerbitan' => (string) ($row['tanggal_penerbitan'] ?? ''),
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
     * Parsing tanggal dari excel
     */
    protected function parseFileDate($value): ?string
    {
        if (!$value) return null;

        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }

        // Jika format "Tempat, Tanggal", ambil setelah koma
        if (is_string($value) && strpos($value, ',') !== false) {
            $value = trim(explode(',', $value)[1]);
        }

        try {
            // Excel numeric date handle
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }

            // Handle Indonesian Month Names (Maret -> March, etc)
            $months = [
                'Januari' => 'January',
                'Februari' => 'February',
                'Maret' => 'March',
                'April' => 'April',
                'Mei' => 'May',
                'Juni' => 'June',
                'Juli' => 'July',
                'Agustus' => 'August',
                'September' => 'September',
                'Oktober' => 'October',
                'November' => 'November',
                'Desember' => 'December'
            ];

            $cleanValue = str_replace(array_keys($months), array_values($months), $value);

            $timestamp = strtotime($cleanValue);
            if ($timestamp) {
                return date('Y-m-d', $timestamp);
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
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
}
