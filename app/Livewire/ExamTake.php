<?php

namespace App\Livewire;

use App\Models\ExamAttempt;
use App\Models\Question;
use App\Services\ExamService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ExamTake extends Component
{
    public ExamAttempt $attempt;

    public $questions = [];

    public $currentQuestionIndex = 0;

    public $answers = [];

    public $flaggedQuestions = [];

    public $timeRemaining = 0;

    public $showSubmitModal = false;

    public $isSubmitting = false;

    public function mount(ExamAttempt $attempt)
    {
        $this->attempt = $attempt->load(['exam.questions.options', 'answers']);

        $this->authorize('view', $this->attempt);

        if ($this->attempt->status !== 'in_progress') {
            return redirect()->route('exam.result', $this->attempt);
        }

        $questions = $this->attempt->exam->questions;
        if ($this->attempt->exam->shuffle_questions) {
            $questions = $questions->shuffle();
        }
        if ($this->attempt->exam->shuffle_options) {
            foreach ($questions as $q) {
                if ($q->relationLoaded('options')) {
                    $q->setRelation('options', $q->options->shuffle());
                }
            }
        }
        $this->questions = $questions->values()->all();
        $this->loadAnswers();
        $this->calculateTimeRemaining();
    }

    public function loadAnswers()
    {
        foreach ($this->attempt->answers as $answer) {
            $this->answers[$answer->question_id] = $answer->option_id;
            if ($answer->answer_text) {
                $this->answers[$answer->question_id.'_text'] = $answer->answer_text;
            }
        }
    }

    public function calculateTimeRemaining()
    {
        if ($this->attempt->ended_at) {
            $this->timeRemaining = max(0, now()->diffInSeconds($this->attempt->ended_at, false));
        }
    }

    #[On('tick')]
    public function checkExpiry()
    {
        $this->calculateTimeRemaining();

        if ($this->timeRemaining <= 0) {
            $this->autoSubmit();
        }
    }

    public function autoSubmit()
    {
        if ($this->attempt->status !== 'in_progress') {
            return;
        }

        app(ExamService::class)->autoSubmit($this->attempt);
        $this->attempt->refresh();

        $this->dispatch('exam-auto-submitted');

        return redirect()->route('exam.result', $this->attempt);
    }

    public function saveAnswer($questionId, $optionId)
    {
        if ($this->attempt->status !== 'in_progress') {
            return;
        }

        $question = Question::find($questionId);
        if (! $question) {
            return;
        }

        app(ExamService::class)->saveAnswer($this->attempt, $question, $optionId);
        $this->answers[$questionId] = $optionId;
    }

    public function toggleFlag($questionId)
    {
        if (in_array($questionId, $this->flaggedQuestions)) {
            $this->flaggedQuestions = array_values(array_diff($this->flaggedQuestions, [$questionId]));
        } else {
            $this->flaggedQuestions[] = $questionId;
        }
    }

    public function goToQuestion($index)
    {
        if ($index >= 0 && $index < count($this->questions)) {
            $this->currentQuestionIndex = $index;
        }
    }

    public function previousQuestion()
    {
        if ($this->currentQuestionIndex > 0) {
            $this->currentQuestionIndex--;
        }
    }

    public function nextQuestion()
    {
        if ($this->currentQuestionIndex < count($this->questions) - 1) {
            $this->currentQuestionIndex++;
        }
    }

    public function submitExam()
    {
        $this->showSubmitModal = true;
    }

    public function confirmSubmit()
    {
        $this->authorize('update', $this->attempt);

        $key = 'exam-submit:'.auth()->id().':'.$this->attempt->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('submit', 'Too many submit attempts. Please wait.');

            return;
        }

        RateLimiter::hit($key, 60);

        if ($this->isSubmitting) {
            return;
        }

        $this->isSubmitting = true;

        app(ExamService::class)->submitAttempt($this->attempt);
        $this->attempt->refresh();

        $this->showSubmitModal = false;
        $this->isSubmitting = false;

        return redirect()->route('exam.result', $this->attempt);
    }

    public function getCurrentQuestionProperty()
    {
        return $this->questions[$this->currentQuestionIndex] ?? null;
    }

    public function getQuestionStatusProperty(): array
    {
        $status = [];
        foreach ($this->questions as $question) {
            $qid = is_object($question) ? $question->id : ($question['id'] ?? null);
            if (! $qid) {
                continue;
            }

            $isAnswered = isset($this->answers[$qid]) && $this->answers[$qid] !== null && $this->answers[$qid] !== '';
            $isFlagged = in_array($qid, $this->flaggedQuestions);

            if ($isFlagged) {
                $status[$qid] = 'flagged';
            } elseif ($isAnswered) {
                $status[$qid] = 'answered';
            } else {
                $status[$qid] = 'unanswered';
            }
        }

        return $status;
    }

    public function getAnsweredCountProperty(): int
    {
        return count(array_filter($this->answers, function ($value, $key) {
            return is_numeric($key) && $value !== null && $value !== '';
        }, ARRAY_FILTER_USE_BOTH));
    }

    public function render()
    {
        return view('livewire.exam-take', [
            'attempt' => $this->attempt,
            'currentQuestion' => $this->currentQuestion,
            'questionStatus' => $this->questionStatus,
            'answeredCount' => $this->answeredCount,
        ]);
    }
}
