<?php

namespace Tests\Feature\Module;

use App\Exceptions\ZipOpenException;
use App\Services\ModuleService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class ModuleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $moduleServiceMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->moduleServiceMock = Mockery::mock(ModuleService::class);
        
        $this->app->instance(ModuleService::class, $this->moduleServiceMock);
    }

    public function test_storeMethod_saveModule_whenValidData(): void
    {
        $moduleId = 1;

        $data = [
            'width' => 100,
            'height' => 100,
            'color' => 'red',
            'link' => 'https://test.com',
        ];

        $this->moduleServiceMock->shouldReceive('storeModule')
            ->once()
            ->with($data)
            ->andReturn($moduleId);

        $response = $this->postJson(route('modules.store'), $data);

        $response->assertCreated();
        $response->assertJson([
            'message' => 'The module successfully created',
            'moduleId' => $moduleId,
        ]);
    }

    public function test_storeMethod_handlesError_whenQueryException(): void
    {
        $data = [
            'width' => 100,
            'height' => 100,
            'color' => 'red',
            'link' => 'https://test.com',
        ];

        $this->moduleServiceMock->shouldReceive('storeModule')
            ->once()
            ->with($data)
            ->andThrow(new QueryException('SQL error', 'insert', [], new \Exception('Simulated error')));

        $response = $this->postJson(route('modules.store'), $data);

        $response->assertStatus(500); 
        $response->assertJson([
            'message' => 'An error occurred while creating the module',
        ]);
    }

    public function test_storeMethod_validationFails_whenInvalidData(): void
    {
        $data = [
            'width' => 'invalid',
            'height' => 100,
            'color' => 'red',
            'link' => 'https://test.com',
        ];

        $response = $this->postJson(route('modules.store'), $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'width'  
        ]);
    }

    public function test_storeMethod_validationFails_whenInvalidColorName(): void
    {
        $data = [
            'width' => 100,
            'height' => 100,
            'color' => 'invalid', 
            'link' => 'https://test.com',
        ];

        $response = $this->postJson(route('modules.store'), $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'color'  
        ]);
    }

    public function test_storeMethod_validationFails_whenInvalidHexColorCode(): void
    {
        $data = [
            'width' => 100,
            'height' => 100,
            'color' => '#FFHH', //invalid hex format 
            'link' => 'https://test.com',
        ];

        $response = $this->postJson(route('modules.store'), $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'color'  
        ]);
    }

    public function test_downloadMethod_returnsBinaryResponse_whenSuccess()
    {
        $moduleId = 1;
        $fakeZipPath = "module_{$moduleId}.zip";

        Storage::fake('modules');
        Storage::disk('modules')->put($fakeZipPath, 'dummy content');

        $this->moduleServiceMock->shouldReceive('getZipFilePath')->with($moduleId)->andReturn(Storage::disk('modules')->path($fakeZipPath));

        $response = $this->get(route('modules.download', ['id' => $moduleId]));

        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);
        Storage::disk('modules')->assertExists($fakeZipPath);
    }

    public function test_downloadMethod_returnsNotFound_whenMissingModule()
    {
        $moduleId = 999;

        $this->moduleServiceMock->shouldReceive('getZipFilePath')->with($moduleId)->andThrow(new ModelNotFoundException());

        $response = $this->get(route('modules.download', ['id' => $moduleId]));

        $response->assertNotFound()
            ->assertJson(["message" => "Module with id: $moduleId not found"]);
    }

    public function test_downloadMethod_handlesZipOpenException()
    {
        $moduleId = 2;

        $this->moduleServiceMock->shouldReceive('getZipFilePath')->with($moduleId)->andThrow(new ZipOpenException());

        $response = $this->get(route('modules.download', ['id' => $moduleId]));

        $response->assertServerError()
            ->assertJson(["message" => "Failed to open zip file"]);
    }
    
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
