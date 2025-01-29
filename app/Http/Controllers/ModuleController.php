<?php

namespace App\Http\Controllers;

use App\Exceptions\ZipOpenException;
use App\Http\Requests\ModuleStoreRequest;
use App\Services\ModuleService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

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
            $zipFilePath = $this->moduleService->getZipFilePath($id);
            
            return response()->download($zipFilePath);
        } catch (ModelNotFoundException $e) {
            return new JsonResponse([
                'message' => "Module with id: $id not found"
            ], 404); 
        } catch (ZipOpenException $e) {
            return new JsonResponse([
                'message' => "Failed to open zip file"
            ], 500); 
        }
    }
}
