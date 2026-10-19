<?php

namespace App\Livewire;

use App\Services\ExamService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ExamDashboard extends Component
{
    public function render()
    {
        $exams = app(ExamService::class)->getAvailableExams(auth()->user());

        return view('livewire.exam-dashboard', [
            'exams' => $exams,
        ]);
    }
}
