<?php 

namespace App\Services;

use App\Exceptions\ZipOpenException;
use App\Models\Module;
use ZipArchive;

class ModuleService {
    private const FILE_DISK = 'modules';

    public function __construct(private FileService $fileService)
    {}

    public function storeModule(array $data): int {
        $module = Module::create($data);

        return $module->id;
    }

    public function getModule(int $moduleId): Module {
        return Module::findOrFail($moduleId);
    }   

    private function generateHtmlContent(Module $module): string {
        return "<!DOCTYPE html>
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
    }

    private function generateCssContent(Module $module): string {
        return "#module_{$module->id} {
                    width: {$module->width}px;
                    height: {$module->height}px;
                    background-color: {$module->color};
                    cursor: pointer;
                }";
    }

    private function generateJsContent(Module $module): string {
        return "document.querySelector('#module_{$module->id}').addEventListener('click', function(e) {
                    window.open('{$module->link}', '_blank');
                });";
    }

    public function generateAndStoreFiles(Module $module): array {
        $htmlFileContent = $this->generateHtmlContent($module);
        $cssFileContent = $this->generateCssContent($module);
        $jsFileContent = $this->generateJsContent($module);

        $htmlFilePath = "module_{$module->id}.html";
        $cssFilePath = "module_{$module->id}.css";
        $jsFilePath = "module_{$module->id}.js";

        $this->fileService->storeFile($htmlFilePath, $htmlFileContent, self::FILE_DISK);
        $this->fileService->storeFile($cssFilePath, $cssFileContent, self::FILE_DISK);
        $this->fileService->storeFile($jsFilePath, $jsFileContent, self::FILE_DISK);

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
        $zipFilePath = $this->fileService->getFilePath($zipFileName, self::FILE_DISK);

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new ZipOpenException("Failed to create ZIP file for module ID: {$moduleId}");
        }

        foreach ($files as $file) {
            $fullPath = $this->fileService->getFilePath($file, self::FILE_DISK);;
            $zip->addFile($fullPath, basename($file));
        }
        $zip->close();

        return $zipFilePath; 
    }
}