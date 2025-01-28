<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModuleStoreRequest;
use App\Services\ModuleService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ModuleController extends Controller
{
    public function __construct(private ModuleService $moduleService)
    {}

    public function store(ModuleStoreRequest $request): JsonResponse {
        try {
            $moduleId = $this->moduleService->storeModule($request->validated());

            return new JsonResponse([
                'message' => 'The module successfully created',
                'moduleId' => $moduleId
            ], 201);
        } catch(QueryException $e) {
            return new JsonResponse([
                'message' => 'An error occurred while creating the module',
            ], 500);
        }
    }

    public function download(int $id) {
        // $moduleId = 1;
        // $fileContent1 = "Module ID: {$moduleId}\nFile 1 content...";
    
        // $filePath1 = "module_{$moduleId}_file1.txt";
    
        // Storage::disk('modules')->put($filePath1, $fileContent1);

        // $absolutePath = Storage::disk('modules')->path($filePath1);
    }
}
