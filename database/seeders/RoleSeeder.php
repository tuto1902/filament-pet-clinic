<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::factory(4)
            ->state(new Sequence(
                ['name' => 'admin', 'description' => 'Admin User'],
                ['name' => 'doctor', 'description' => 'Pet Doctor'],
                ['name' => 'owner', 'description' => 'Pet Owner'],
                ['name' => 'staff', 'description' => 'Clinic Staff'],
            ))
            ->create();
    }
}
