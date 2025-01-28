<?php 

namespace App\Services;

use App\Models\Module;

class ModuleService {
    public function storeModule(array $data): int {
        $module = Module::create($data);

        return $module->id;
    }
}