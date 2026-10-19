<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'code' => strtoupper(fake()->unique()->bothify('EXAM-###??')),
            'description' => fake()->paragraph(),
            'duration_minutes' => 60,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDays(7),
            'shuffle_questions' => false,
            'shuffle_options' => false,
            'max_attempts' => 1,
            'pass_mark' => 50.00,
            'show_result_at_end' => true,
            'is_published' => true,
            'proctor_id' => User::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['is_published' => true]);
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['is_published' => false]);
    }
}
