<?php

namespace App\Providers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Policies\ExamAttemptPolicy;
use App\Policies\ExamPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Exam::class => ExamPolicy::class,
        ExamAttempt::class => ExamAttemptPolicy::class,
    ];
}
