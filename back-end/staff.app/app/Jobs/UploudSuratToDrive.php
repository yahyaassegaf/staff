<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\GoogleDrive;

class UploudSuratToDrive implements ShouldQueue
{
    use Queueable;

    public $tries = 5;
    public $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $id,
        public string $nameTable,
        public string $prodiName,
        public string $modelClass
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Handling upload for {$this->nameTable}, ID: {$this->id}");

        $model = $this->modelClass;
        $file = $model::find($this->id);

        if (!$file) {
            Log::error("File record not found for model {$this->modelClass} with ID {$this->id}");
            return;
        }

        try {
            $filePath = $file->local_path;
            if (!file_exists($filePath)) {
                Log::error("Local file does not exist at: {$filePath}");
                return;
            }

            $fileName = basename($filePath);
            $root = config('filesystems.disks.google.folder');

            $prodiFolder = GoogleDrive::getOrCreateDriveFolder(
                "{$this->prodiName}",
                $root
            );

            $suratFolder = GoogleDrive::getOrCreateDriveFolder(
                "{$this->nameTable}",
                $prodiFolder
            );

            $fileId = Storage::disk('google')->put(
                "{$suratFolder}/{$fileName}",
                fopen($filePath, 'r')
            );

            Storage::disk('google')->setVisibility($fileId, 'public');

            $file->update([
                'drive_file_id' => $fileId,
                'drive_link' => "https://drive.google.com/file/d/{$fileId}/view",
                'status' => 'uploaded'
            ]);

            Log::info("Successfully uploaded {$fileName} to Drive");
        } catch (\Throwable $e) {
            $file->update(['status' => 'failed']);
            Log::error("Upload failed for {$this->nameTable}: " . $e->getMessage());
            throw $e;
        }
    }
}
