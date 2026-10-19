<?php

namespace Tests\Feature;

use App\Filament\Resources\ExamAttemptResource;
use App\Filament\Resources\ExamResource;
use App\Filament\Resources\OptionResource;
use App\Filament\Resources\QuestionResource;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_filament_resources_can_be_resolved(): void
    {
        $resources = [
            ExamResource::class,
            QuestionResource::class,
            OptionResource::class,
            ExamAttemptResource::class,
            UserResource::class,
        ];

        foreach ($resources as $resource) {
            $this->assertNotEmpty($resource::getModel());
            $this->assertNotEmpty($resource::getNavigationIcon());
            $this->assertNotEmpty($resource::getNavigationGroup());
            $this->assertIsArray($resource::getPages());
        }
    }

    public function test_admin_can_access_filament_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertSuccessful();
    }

    public function test_admin_can_access_filament_exam_resource_index(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(ExamResource::getUrl('index'));

        $response->assertSuccessful();
    }

    public function test_admin_can_access_filament_user_resource_index(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(UserResource::getUrl('index'));

        $response->assertSuccessful();
    }

    public function test_admin_can_access_filament_question_resource_index(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(QuestionResource::getUrl('index'));

        $response->assertSuccessful();
    }

    public function test_admin_can_access_filament_exam_attempt_resource_index(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(ExamAttemptResource::getUrl('index'));

        $response->assertSuccessful();
    }
}
