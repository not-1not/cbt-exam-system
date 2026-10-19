<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_student_can_authenticate_and_redirect_to_exam_dashboard(): void
    {
        $student = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);
        $student->assignRole('student');

        $response = $this->post('/login', [
            'email' => $student->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($student);
        $response->assertRedirect(route('exam.dashboard'));
    }

    public function test_admin_can_authenticate_and_redirect_to_admin(): void
    {
        $admin = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);
        $admin->assignRole('admin');

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect('/admin');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_as_student(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Student',
            'email' => 'newstudent@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $user = User::where('email', 'newstudent@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('student'));
        $response->assertRedirect(route('exam.dashboard'));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
