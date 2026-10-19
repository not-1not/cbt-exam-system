<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'code', 'description', 'duration_minutes', 'start_at', 'end_at', 'shuffle_questions', 'shuffle_options', 'max_attempts', 'pass_mark', 'show_result_at_end', 'is_published', 'proctor_id'])]
#[Hidden([])]
class Exam extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'is_published' => 'boolean',
            'show_result_at_end' => 'boolean',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function proctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proctor_id');
    }

    public function isOpen(): bool
    {
        $now = Carbon::now();

        if ($this->start_at && $now->lt($this->start_at)) {
            return false;
        }

        if ($this->end_at && $now->gt($this->end_at)) {
            return false;
        }

        return true;
    }
}
