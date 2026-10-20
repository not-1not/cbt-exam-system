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
        $service = app(ExamService::class);
        $user = auth()->user();

        $activeAttempts = $service->getActiveAttempts($user);
        $exams = $service->getAvailableExams($user);
        $completedAttempts = $service->getCompletedAttempts($user);

        return view('livewire.exam-dashboard', [
            'activeAttempts' => $activeAttempts,
            'exams' => $exams,
            'completedAttempts' => $completedAttempts,
        ]);
    }
}
