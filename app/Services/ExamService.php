<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAnswer;
use App\Models\Question;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamService
{
    public function startAttempt(User $user, Exam $exam): ExamAttempt
    {
        $inProgressAttempt = ExamAttempt::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->where('status', 'in_progress')
            ->first();

        if ($inProgressAttempt) {
            if ($inProgressAttempt->ended_at && Carbon::now()->gt($inProgressAttempt->ended_at)) {
                $this->autoSubmit($inProgressAttempt);
            } else {
                return $inProgressAttempt->load('exam.questions.options');
            }
        }

        $this->validateCanStartAttempt($user, $exam);

        return DB::transaction(function () use ($user, $exam) {
            $attempt = ExamAttempt::create([
                'user_id' => $user->id,
                'exam_id' => $exam->id,
                'status' => 'in_progress',
                'started_at' => Carbon::now(),
                'ended_at' => Carbon::now()->addMinutes($exam->duration_minutes),
            ]);

            return $attempt->load('exam.questions.options');
        });
    }

    public function validateCanStartAttempt(User $user, Exam $exam): void
    {
        if (! $exam->is_published) {
            throw ValidationException::withMessages(['exam' => 'Ujian belum dipublikasikan.']);
        }

        $now = Carbon::now();

        if ($exam->start_at && $now->lt($exam->start_at)) {
            throw ValidationException::withMessages(['exam' => 'Ujian belum dimulai.']);
        }

        if ($exam->end_at && $now->gt($exam->end_at)) {
            throw ValidationException::withMessages(['exam' => 'Ujian sudah berakhir.']);
        }

        $completedAttemptsCount = ExamAttempt::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->where('status', 'completed')
            ->count();

        if ($exam->max_attempts && $completedAttemptsCount >= $exam->max_attempts) {
            throw ValidationException::withMessages(['exam' => 'Maksimal percobaan sudah tercapai.']);
        }
    }

    public function saveAnswer(ExamAttempt $attempt, Question $question, ?int $optionId = null, ?string $answerText = null): ExamAttemptAnswer
    {
        if ($attempt->status !== 'in_progress') {
            throw ValidationException::withMessages(['attempt' => 'Ujian tidak dalam status berlangsung.']);
        }

        if ($attempt->ended_at && Carbon::now()->gt($attempt->ended_at)) {
            $this->autoSubmit($attempt);
            throw ValidationException::withMessages(['attempt' => 'Waktu ujian sudah habis.']);
        }

        $option = null;
        if ($optionId) {
            $option = $question->options()->where('id', $optionId)->first();
            if (! $option) {
                throw ValidationException::withMessages([
                    'option_id' => 'Pilihan jawaban tidak valid untuk soal ini.',
                ]);
            }
        }

        $isCorrect = $option ? (bool) $option->is_correct : false;
        $pointsEarned = $isCorrect ? $question->points : 0;

        return ExamAttemptAnswer::updateOrCreate(
            [
                'exam_attempt_id' => $attempt->id,
                'question_id' => $question->id,
            ],
            [
                'option_id' => $option ? $option->id : null,
                'answer_text' => $answerText,
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
            ]
        );
    }

    public function checkExpiry(ExamAttempt $attempt): bool
    {
        if ($attempt->status !== 'in_progress') {
            return false;
        }

        if ($attempt->ended_at && Carbon::now()->gt($attempt->ended_at)) {
            $this->autoSubmit($attempt);

            return true;
        }

        return false;
    }

    public function autoSubmit(ExamAttempt $attempt): ExamAttempt
    {
        $attempt->update([
            'status' => 'completed',
            'submitted_at' => Carbon::now(),
            'force_submitted_at' => Carbon::now(),
            'force_reason' => 'Waktu ujian habis',
            'score' => $this->calculateScore($attempt),
        ]);

        return $attempt;
    }

    public function submitAttempt(ExamAttempt $attempt): ExamAttempt
    {
        if ($attempt->status !== 'in_progress') {
            throw ValidationException::withMessages(['attempt' => 'Ujian tidak dalam status berlangsung.']);
        }

        $this->checkExpiry($attempt);

        $attempt->update([
            'status' => 'completed',
            'submitted_at' => Carbon::now(),
            'score' => $this->calculateScore($attempt),
        ]);

        return $attempt;
    }

    public function calculateScore(ExamAttempt $attempt): float
    {
        $totalQuestions = $attempt->exam->questions()->count();
        if ($totalQuestions <= 0) {
            return 0.0;
        }

        $correctAnswers = $attempt->answers()->where('is_correct', true)->count();

        return round(($correctAnswers / $totalQuestions) * 100, 2);
    }

    public function getActiveAttempts(User $user): Collection
    {
        $attempts = ExamAttempt::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->with(['exam.questions'])
            ->get();

        return $attempts->filter(function (ExamAttempt $attempt) {
            if ($attempt->ended_at && Carbon::now()->gt($attempt->ended_at)) {
                $this->autoSubmit($attempt);

                return false;
            }

            return true;
        })->values();
    }

    public function getCompletedAttempts(User $user): Collection
    {
        return ExamAttempt::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('exam')
            ->latest('submitted_at')
            ->get();
    }

    public function getAvailableExams(User $user): Collection
    {
        $now = Carbon::now();

        return Exam::where('is_published', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->withCount(['questions', 'attempts as completed_attempts_count' => function ($query) use ($user) {
                $query->where('user_id', $user->id)->where('status', 'completed');
            }])
            ->get()
            ->filter(function ($exam) use ($user) {
                if ($exam->max_attempts) {
                    $completedCount = ExamAttempt::where('user_id', $user->id)
                        ->where('exam_id', $exam->id)
                        ->where('status', 'completed')
                        ->count();

                    return $completedCount < $exam->max_attempts;
                }

                return true;
            })
            ->values();
    }
}
