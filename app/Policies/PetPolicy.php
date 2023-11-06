<?php

namespace App\Policies;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pet $pet): bool
    {
        return match ($user->role->name) {
            'admin', 'doctor' => true,
            'owner' => $user->id == $pet->owner_id
        };
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pet $pet): bool
    {
        return match ($user->role->name) {
            'admin', 'doctor' => true,
            'owner' => $user->id == $pet->owner_id
        };
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pet $pet): bool
    {
        return match ($user->role->name) {
            'admin', 'doctor' => true,
            'owner' => $user->id == $pet->owner_id
        };
    }
}
