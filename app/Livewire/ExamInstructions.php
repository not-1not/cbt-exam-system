<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Services\ExamService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ExamInstructions extends Component
{
    public Exam $exam;

    public function mount(Exam $exam)
    {
        $this->exam = $exam->load(['questions.options'])->loadCount('questions');
    }

    public function startExam()
    {
        $this->authorize('start', $this->exam);

        $key = 'exam-start:'.auth()->id().':'.$this->exam->id;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->addError('exam', 'Too many attempts. Please wait before trying again.');

            return;
        }

        RateLimiter::hit($key, 60);

        $attempt = app(ExamService::class)->startAttempt(auth()->user(), $this->exam);

        return redirect()->route('exam.take', $attempt);
    }

    public function render()
    {
        return view('livewire.exam-instructions', [
            'exam' => $this->exam,
        ]);
    }
}
