<?php

namespace App\Livewire;

use App\Models\ExamAttempt;
use App\Services\ExamService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ExamResult extends Component
{
    public ExamAttempt $attempt;

    public function mount(ExamAttempt $attempt)
    {
        $this->attempt = $attempt->load(['exam.questions.options', 'answers.option', 'answers.question.options', 'user']);

        $this->authorize('view', $this->attempt);

        if ($this->attempt->status === 'in_progress') {
            return redirect()->route('exam.take', $this->attempt);
        }

        // Clean up any corrupt legacy answers where option_id did not belong to question_id
        $hasCorrupted = false;
        foreach ($this->attempt->answers as $ans) {
            if ($ans->option && $ans->option->question_id !== $ans->question_id) {
                $ans->update(['option_id' => null, 'is_correct' => false, 'points_earned' => 0]);
                $hasCorrupted = true;
            }
        }
        if ($hasCorrupted) {
            $this->attempt->load('answers.option');
        }

        if (($this->attempt->score === null || $hasCorrupted) && in_array($this->attempt->status, ['completed', 'submitted', 'force_submitted', 'graded'])) {
            $freshScore = app(ExamService::class)->calculateScore($this->attempt);
            $this->attempt->update(['score' => $freshScore]);
            $this->attempt->score = $freshScore;
        }
    }

    public function getPassStatusProperty(): ?string
    {
        if (! $this->attempt->exam->pass_mark) {
            return null;
        }

        return $this->attempt->score >= $this->attempt->exam->pass_mark ? 'lulus' : 'tidak_lulus';
    }

    public function getTotalQuestionsProperty(): int
    {
        return $this->attempt->exam->questions->count();
    }

    public function getCorrectCountProperty(): int
    {
        return $this->attempt->answers->where('is_correct', true)->count();
    }

    public function getAnsweredCountProperty(): int
    {
        return $this->attempt->answers->filter(fn ($a) => $a->option_id !== null || ! empty($a->answer_text))->count();
    }

    public function getIncorrectCountProperty(): int
    {
        return $this->attempt->answers->where('is_correct', false)->filter(fn ($a) => $a->option_id !== null || ! empty($a->answer_text))->count();
    }

    public function getUnansweredCountProperty(): int
    {
        return max(0, $this->totalQuestions - $this->answeredCount);
    }

    public function getTotalPointsProperty(): float
    {
        return 100.00;
    }

    public function render()
    {
        return view('livewire.exam-result', [
            'attempt' => $this->attempt,
            'passStatus' => $this->passStatus,
            'totalPoints' => $this->totalPoints,
            'totalQuestions' => $this->totalQuestions,
            'correctCount' => $this->correctCount,
            'incorrectCount' => $this->incorrectCount,
            'unansweredCount' => $this->unansweredCount,
        ]);
    }
}
