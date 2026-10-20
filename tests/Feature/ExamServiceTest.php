<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Option;
use App\Models\Question;
use App\Models\User;
use App\Services\ExamService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ExamServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ExamService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->service = app(ExamService::class);
    }

    public function test_student_can_start_attempt_on_valid_exam(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $exam = Exam::factory()->create([
            'is_published' => true,
            'duration_minutes' => 45,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
        ]);

        $attempt = $this->service->startAttempt($student, $exam);

        $this->assertInstanceOf(ExamAttempt::class, $attempt);
        $this->assertEquals('in_progress', $attempt->status);
        $this->assertEquals($student->id, $attempt->user_id);
        $this->assertEquals($exam->id, $attempt->exam_id);
        $this->assertNotNull($attempt->started_at);
        $this->assertNotNull($attempt->ended_at);
    }

    public function test_cannot_start_attempt_on_unpublished_exam(): void
    {
        $this->expectException(ValidationException::class);

        $student = User::factory()->create();
        $student->assignRole('student');

        $exam = Exam::factory()->unpublished()->create();

        $this->service->startAttempt($student, $exam);
    }

    public function test_cannot_start_attempt_if_already_has_in_progress_attempt(): void
    {
        $this->expectException(ValidationException::class);

        $student = User::factory()->create();
        $student->assignRole('student');

        $exam = Exam::factory()->create();

        $this->service->startAttempt($student, $exam);
        // Second attempt while first is in progress
        $this->service->startAttempt($student, $exam);
    }

    public function test_cannot_start_attempt_exceeding_max_attempts(): void
    {
        $this->expectException(ValidationException::class);

        $student = User::factory()->create();
        $student->assignRole('student');

        $exam = Exam::factory()->create(['max_attempts' => 1]);

        $attempt = $this->service->startAttempt($student, $exam);
        $this->service->submitAttempt($attempt);

        // Attempt second time
        $this->service->startAttempt($student, $exam);
    }

    public function test_save_answer_calculates_points_and_is_correct(): void
    {
        $student = User::factory()->create();
        $exam = Exam::factory()->create();
        $question = Question::factory()->create([
            'exam_id' => $exam->id,
            'points' => 20,
        ]);

        $correctOption = Option::factory()->correct()->create(['question_id' => $question->id]);
        $wrongOption = Option::factory()->create(['question_id' => $question->id, 'is_correct' => false]);

        $attempt = $this->service->startAttempt($student, $exam);

        // Save correct answer
        $answer = $this->service->saveAnswer($attempt, $question, $correctOption->id);
        $this->assertTrue($answer->is_correct);
        $this->assertEquals(20, $answer->points_earned);

        // Switch to wrong answer
        $answer2 = $this->service->saveAnswer($attempt, $question, $wrongOption->id);
        $this->assertFalse($answer2->is_correct);
        $this->assertEquals(0, $answer2->points_earned);
        $this->assertEquals($answer->id, $answer2->id); // Updated same record
    }

    public function test_calculate_score_returns_percentage(): void
    {
        $student = User::factory()->create();
        $exam = Exam::factory()->create();

        $q1 = Question::factory()->create(['exam_id' => $exam->id, 'points' => 10]);
        $q2 = Question::factory()->create(['exam_id' => $exam->id, 'points' => 10]);

        $opt1 = Option::factory()->correct()->create(['question_id' => $q1->id]);
        $opt2 = Option::factory()->create(['question_id' => $q2->id, 'is_correct' => false]);

        $attempt = $this->service->startAttempt($student, $exam);
        $this->service->saveAnswer($attempt, $q1, $opt1->id); // 10 pts
        $this->service->saveAnswer($attempt, $q2, $opt2->id); // 0 pts

        // Total questions = 2, correct = 1 -> Score = 50.00%
        $score = $this->service->calculateScore($attempt);
        $this->assertEquals(50.00, $score);
    }

    public function test_calculate_score_uses_correct_over_total_questions_percentage_formula(): void
    {
        $student = User::factory()->create();
        $exam = Exam::factory()->create();

        // 4 questions with unequal points to verify that points are ignored in favor of count
        $q1 = Question::factory()->create(['exam_id' => $exam->id, 'points' => 50]);
        $q2 = Question::factory()->create(['exam_id' => $exam->id, 'points' => 10]);
        $q3 = Question::factory()->create(['exam_id' => $exam->id, 'points' => 5]);
        $q4 = Question::factory()->create(['exam_id' => $exam->id, 'points' => 5]);

        $opt1 = Option::factory()->correct()->create(['question_id' => $q1->id]);
        $opt2 = Option::factory()->correct()->create(['question_id' => $q2->id]);
        $opt3 = Option::factory()->correct()->create(['question_id' => $q3->id]);
        $opt4 = Option::factory()->create(['question_id' => $q4->id, 'is_correct' => false]);

        $attempt = $this->service->startAttempt($student, $exam);
        $this->service->saveAnswer($attempt, $q1, $opt1->id); // benar
        $this->service->saveAnswer($attempt, $q2, $opt2->id); // benar
        $this->service->saveAnswer($attempt, $q3, $opt3->id); // benar
        $this->service->saveAnswer($attempt, $q4, $opt4->id); // salah

        // 3 benar dari 4 soal -> (3 / 4) * 100 = 75.00%
        $score = $this->service->calculateScore($attempt);
        $this->assertEquals(75.00, $score);
    }

    public function test_save_answer_rejects_option_from_different_question(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $student = User::factory()->create();
        $exam = Exam::factory()->create();

        $q1 = Question::factory()->create(['exam_id' => $exam->id]);
        $q2 = Question::factory()->create(['exam_id' => $exam->id]);

        $optForQ2 = Option::factory()->create(['question_id' => $q2->id]);

        $attempt = $this->service->startAttempt($student, $exam);

        // Attempting to save Q2's option for Q1 must throw ValidationException!
        $this->service->saveAnswer($attempt, $q1, $optForQ2->id);
    }

    public function test_submit_attempt_marks_completed_and_saves_score(): void
    {
        $student = User::factory()->create();
        $exam = Exam::factory()->create();
        $q = Question::factory()->create(['exam_id' => $exam->id, 'points' => 10]);
        $opt = Option::factory()->correct()->create(['question_id' => $q->id]);

        $attempt = $this->service->startAttempt($student, $exam);
        $this->service->saveAnswer($attempt, $q, $opt->id);

        $submitted = $this->service->submitAttempt($attempt);

        $this->assertEquals('completed', $submitted->status);
        $this->assertNotNull($submitted->submitted_at);
        $this->assertEquals(100.00, $submitted->score);
    }

    public function test_auto_submit_records_timeout_reason(): void
    {
        $student = User::factory()->create();
        $exam = Exam::factory()->create();
        $attempt = $this->service->startAttempt($student, $exam);

        $submitted = $this->service->autoSubmit($attempt);

        $this->assertEquals('completed', $submitted->status);
        $this->assertNotNull($submitted->force_submitted_at);
        $this->assertEquals('Waktu ujian habis', $submitted->force_reason);
    }

    public function test_get_available_exams_filters_properly(): void
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $activeExam = Exam::factory()->create(['is_published' => true]);
        $unpublishedExam = Exam::factory()->unpublished()->create();
        $expiredExam = Exam::factory()->create([
            'is_published' => true,
            'start_at' => now()->subDays(5),
            'end_at' => now()->subDay(),
        ]);

        $available = $this->service->getAvailableExams($student);

        $this->assertTrue($available->contains($activeExam));
        $this->assertFalse($available->contains($unpublishedExam));
        $this->assertFalse($available->contains($expiredExam));
    }
}
