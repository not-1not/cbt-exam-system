<?php

namespace Database\Factories;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Option>
 */
class OptionFactory extends Factory
{
    protected $model = Option::class;

    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'text' => fake()->sentence(3),
            'is_correct' => false,
            'order' => fake()->numberBetween(1, 4),
        ];
    }

    public function correct(): static
    {
        return $this->state(fn () => ['is_correct' => true]);
    }
}
