<?php

namespace App\Policies;

use App\Models\Gig;
use App\Models\User;

class GigPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Gig $gig): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Gig $gig): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Gig $gig): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Gig $gig): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Gig $gig): bool
    {
        return $user->isAdmin();
    }
}
