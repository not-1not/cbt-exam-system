<?php

namespace App\Livewire;

use App\Models\ExamAttempt;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ExamResult extends Component
{
    public ExamAttempt $attempt;

    public function mount(ExamAttempt $attempt)
    {
        $this->attempt = $attempt->load(['exam', 'answers.question.options', 'user']);

        $this->authorize('view', $this->attempt);

        if ($this->attempt->status === 'in_progress') {
            return redirect()->route('exam.take', $this->attempt);
        }
    }

    public function getPassStatusProperty()
    {
        if (! $this->attempt->exam->pass_mark) {
            return null;
        }

        return $this->attempt->score >= $this->attempt->exam->pass_mark ? 'lulus' : 'tidak_lulus';
    }

    public function getTotalPointsProperty()
    {
        return $this->attempt->exam->questions->sum('points');
    }

    public function render()
    {
        return view('livewire.exam-result', [
            'attempt' => $this->attempt,
            'passStatus' => $this->passStatus,
            'totalPoints' => $this->totalPoints,
        ]);
    }
}
