<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class GoogleDrive
{
    public static function getOrCreateDriveFolder(string $name, string $parentId)
    {
        $disk = Storage::disk('google');

        foreach ($disk->listContents($parentId, false) as $item) {
            if ($item['type'] === 'dir' && $item['path'] === $name) {
                return $item['path']; // folder_id
            }
        }

        return $disk->makeDirectory("{$parentId}/{$name}");
    }
}
