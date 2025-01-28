<?php 

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Facades\Storage;

class ModuleService {
    private const FILE_DISK = 'modules';

    public function storeModule(array $data): int {
        $module = Module::create($data);

        return $module->id;
    }

    public function getModule(int $moduleId) : Module {
        return Module::findOrFail($moduleId);
    }   

    public function generateAndStoreFiles(Module $module) {
        $htmlFileContent = 
            "<!DOCTYPE html>
            <html lang=\"en\">
            <head>
                <meta charset=\"UTF-8\">
                <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
                <title>Module Page</title>
                <link rel=\"stylesheet\" href=\"style.css\">
            </head>
            <body>
                <div id=\"module_{$module->id}\"></div>
            </body>
            </html>";
        $htmlFilePath = "module_{$module->id}.html";

        $cssFileContent = 
            "#module_{$module->id} {
                width: {$module->width}px;
                height: {$module->height}px;
                color: {$module->color};
            }";
        $cssFilePath = "module_{$module->id}.css";

        $jsFileContent = "test";
        
        $jsFileContent = 
            "document.querySelector('#module_{$module->id}').addEventListener('click', function(e) {
                window.location.href = '{$module->link}';
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
}