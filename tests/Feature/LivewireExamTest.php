<?php

namespace Tests\Feature;

use App\Livewire\ExamDashboard;
use App\Livewire\ExamInstructions;
use App\Livewire\ExamResult;
use App\Livewire\ExamTake;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Option;
use App\Models\Question;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireExamTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_student_can_view_exam_dashboard(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $exam = Exam::factory()->create(['is_published' => true]);

        Livewire::actingAs($student)
            ->test(ExamDashboard::class)
            ->assertStatus(200)
            ->assertSee($exam->title);
    }

    public function test_student_can_view_instructions_and_start_exam(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $exam = Exam::factory()->create([
            'is_published' => true,
            'duration_minutes' => 30,
        ]);

        $q = Question::factory()->create(['exam_id' => $exam->id]);
        Option::factory()->create(['question_id' => $q->id]);

        Livewire::actingAs($student)
            ->test(ExamInstructions::class, ['exam' => $exam])
            ->assertStatus(200)
            ->assertSee($exam->title)
            ->call('startExam')
            ->assertRedirect();

        $attempt = ExamAttempt::where('user_id', $student->id)->where('exam_id', $exam->id)->first();
        $this->assertNotNull($attempt);
        $this->assertEquals('in_progress', $attempt->status);
    }

    public function test_student_can_take_exam_save_answers_and_submit(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $exam = Exam::factory()->create(['is_published' => true, 'pass_mark' => 50.00]);
        $q = Question::factory()->create(['exam_id' => $exam->id, 'points' => 10]);
        $opt = Option::factory()->correct()->create(['question_id' => $q->id]);

        $attempt = ExamAttempt::factory()->create([
            'user_id' => $student->id,
            'exam_id' => $exam->id,
            'status' => 'in_progress',
        ]);

        Livewire::actingAs($student)
            ->test(ExamTake::class, ['attempt' => $attempt])
            ->assertStatus(200)
            ->call('saveAnswer', $q->id, $opt->id)
            ->call('toggleFlag', $q->id)
            ->assertSet('flaggedQuestions', [$q->id])
            ->call('confirmSubmit')
            ->assertRedirect(route('exam.result', $attempt));

        $attempt->refresh();
        $this->assertEquals('completed', $attempt->status);
        $this->assertEquals(100.00, $attempt->score);
    }

    public function test_student_can_view_exam_result(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $exam = Exam::factory()->create(['pass_mark' => 60.00]);
        $q = Question::factory()->create(['exam_id' => $exam->id, 'points' => 100]);

        $attempt = ExamAttempt::factory()->completed()->create([
            'user_id' => $student->id,
            'exam_id' => $exam->id,
            'score' => 85.00,
        ]);

        Livewire::actingAs($student)
            ->test(ExamResult::class, ['attempt' => $attempt])
            ->assertStatus(200)
            ->assertSee('85.00')
            ->assertSee('LULUS');
    }

    public function test_force_submitted_exam_does_not_infinite_redirect(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $exam = Exam::factory()->create();

        $attempt = ExamAttempt::factory()->create([
            'user_id' => $student->id,
            'exam_id' => $exam->id,
            'status' => 'force_submitted',
            'force_submitted_at' => now(),
            'score' => 40.00,
        ]);

        Livewire::actingAs($student)
            ->test(ExamResult::class, ['attempt' => $attempt])
            ->assertStatus(200);
    }
}
