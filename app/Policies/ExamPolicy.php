<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\User;

class ExamPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Exam $exam): bool
    {
        return $exam->is_published
            || $user->hasAnyRole(['admin', 'proctor', 'teacher'])
            || $user->id === $exam->proctor_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'proctor', 'teacher']);
    }

    public function update(User $user, Exam $exam): bool
    {
        return $user->hasAnyRole(['admin', 'proctor'])
            || $user->id === $exam->proctor_id;
    }

    public function delete(User $user, Exam $exam): bool
    {
        return $user->hasRole('admin');
    }

    public function start(User $user, Exam $exam): bool
    {
        return $exam->is_published
            && $exam->isOpen()
            && ! $user->hasRole('admin');
    }
}
