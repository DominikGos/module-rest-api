<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModuleStoreRequest;
use App\Services\ModuleService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
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

    public function download(int $id): mixed  {
        try {
            $module = $this->moduleService->getModule($id);

            [$htmlPath, $cssPath, $jsPath] = $this->moduleService->generateAndStoreFiles($module);
            
            return Storage::disk('modules')->download($jsPath);
        } catch (ModelNotFoundException $e) {
            return new JsonResponse([
                'message' => "Module with id: $id not found"
            ], 404); 
        } 
    }
}
