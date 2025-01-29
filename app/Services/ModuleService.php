<?php 

namespace App\Services;

use App\Exceptions\ZipOpenException;
use App\Models\Module;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ModuleService {
    private const FILE_DISK = 'modules';

    public function storeModule(array $data): int {
        $module = Module::create($data);

        return $module->id;
    }

    public function getModule(int $moduleId): Module {
        return Module::findOrFail($moduleId);
    }   

    public function generateAndStoreFiles(Module $module): array {
        $htmlFileContent = 
            "<!DOCTYPE html>
            <html lang=\"en\">
            <head>
                <meta charset=\"UTF-8\">
                <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
                <title>Module Page</title>
                <link rel=\"stylesheet\" href=\"module_{$module->id}.css\">
            </head>
            <body>
                <div id=\"module_{$module->id}\"></div>
                <script src=\"module_{$module->id}.js\"></script>
            </body>
            </html>";
        $htmlFilePath = "module_{$module->id}.html";

        $cssFileContent = 
            "#module_{$module->id} {
                width: {$module->width}px;
                height: {$module->height}px;
                background-color: {$module->color};
                cursor: pointer;
            }";
        $cssFilePath = "module_{$module->id}.css";

        $jsFileContent = "test";
        
        $jsFileContent = 
            "document.querySelector('#module_{$module->id}').addEventListener('click', function(e) {
                window.open('{$module->link}', '_blank');
            });";
        $jsFilePath = "module_{$module->id}.js";

        Storage::disk(ModuleService::FILE_DISK)->put($htmlFilePath, $htmlFileContent);
        Storage::disk(ModuleService::FILE_DISK)->put($cssFilePath, $cssFileContent);
        Storage::disk(ModuleService::FILE_DISK)->put($jsFilePath, $jsFileContent);

        return [
            $htmlFilePath,
            $cssFilePath,
            $jsFilePath
        ];
    }

    public function getZipFilePath(int $moduleId): string {
        $module = $this->getModule($moduleId);
        $files = $this->generateAndStoreFiles($module);

        $zipFileName = "module_{$moduleId}.zip";
        $zipFilePath = Storage::disk(self::FILE_DISK)->path($zipFileName);

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new ZipOpenException("Failed to create ZIP file for module ID: {$moduleId}");
        }

        foreach ($files as $file) {
            $fullPath = Storage::disk(self::FILE_DISK)->path($file);
            $zip->addFile($fullPath, basename($file));
        }
        $zip->close();

        return $zipFilePath; 
    }
}