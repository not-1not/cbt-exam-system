<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'text' => fake()->sentence(6).'?',
            'type' => 'multiple_choice',
            'points' => 10,
            'order' => fake()->numberBetween(1, 20),
        ];
    }
}
