<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamAttempt>
 */
class ExamAttemptFactory extends Factory
{
    protected $model = ExamAttempt::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'exam_id' => Exam::factory(),
            'status' => 'in_progress',
            'started_at' => now(),
            'ended_at' => now()->addMinutes(60),
            'submitted_at' => null,
            'score' => null,
            'force_submitted_at' => null,
            'force_reason' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'submitted_at' => now(),
            'score' => 85.00,
        ]);
    }
}
