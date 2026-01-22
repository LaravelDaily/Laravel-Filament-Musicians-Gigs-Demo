<?php

namespace App\Policies;

use App\Models\GigAssignment;
use App\Models\User;

class GigAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, GigAssignment $gigAssignment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $gigAssignment->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, GigAssignment $gigAssignment): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, GigAssignment $gigAssignment): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, GigAssignment $gigAssignment): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, GigAssignment $gigAssignment): bool
    {
        return $user->isAdmin();
    }

    public function respond(User $user, GigAssignment $gigAssignment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $gigAssignment->user_id;
    }
}
