<?php 

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FileService {
    public function storeFile(string $filePath, string $content, string $fileDisk = 'local'): void
    {
        Storage::disk($fileDisk)->put($filePath, $content);
    }

    public function getFilePath(string $fileName, string $fileDisk = 'local'): string {
        return Storage::disk($fileDisk)->path($fileName);
    }
}