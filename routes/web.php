<?php

use App\Livewire\ExamDashboard;
use App\Livewire\ExamInstructions;
use App\Livewire\ExamResult;
use App\Livewire\ExamTake;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware('role:student')->group(function () {
        Route::get('/exam', ExamDashboard::class)->name('exam.dashboard');
        Route::get('/exam/{exam}/instructions', ExamInstructions::class)->name('exam.instructions');
        Route::get('/exam/attempt/{attempt}/take', ExamTake::class)->name('exam.take');
        Route::get('/exam/attempt/{attempt}/result', ExamResult::class)->name('exam.result');
    });
});

require __DIR__.'/auth.php';
