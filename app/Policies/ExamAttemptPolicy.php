<?php

namespace App\Policies;

use App\Models\ExamAttempt;
use App\Models\User;

class ExamAttemptPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'proctor', 'teacher']);
    }

    public function view(User $user, ExamAttempt $examAttempt): bool
    {
        return $user->hasAnyRole(['admin', 'proctor'])
            || $user->id === $examAttempt->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'proctor', 'teacher']);
    }

    public function update(User $user, ExamAttempt $examAttempt): bool
    {
        return $user->hasAnyRole(['admin', 'proctor'])
            || ($user->id === $examAttempt->user_id && $examAttempt->status === 'in_progress');
    }

    public function delete(User $user, ExamAttempt $examAttempt): bool
    {
        return $user->hasRole('admin');
    }

    public function forceSubmit(User $user, ExamAttempt $examAttempt): bool
    {
        return $user->hasAnyRole(['admin', 'proctor']);
    }
}
