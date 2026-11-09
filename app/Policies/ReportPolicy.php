<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canModerate();
    }

    public function view(User $user, Report $report): bool
    {
        return $user->canModerate();
    }

    /**
     * Жалобу создаёт любой авторизованный пользователь (публичный POST /reports).
     */
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Report $report): bool
    {
        return $user->canModerate();
    }

    public function delete(User $user, Report $report): bool
    {
        return $user->isAdmin();
    }
}
