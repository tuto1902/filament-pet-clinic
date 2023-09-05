<?php

namespace App\Policies;

use App\Models\User;

class ClinicPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function create()
    {
        return auth()->user()->role->name == 'admin';
    }

    public function update()
    {
        return auth()->user()->role->name == 'admin';
    }
}
