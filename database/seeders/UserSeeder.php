<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->role('admin')->create([
            'name' => 'Admin',
            'email' => 'admin@email.com',
            'phone' => '5555551234'
        ]);

        User::factory()->role('owner')->create([
            'name' => 'Owner',
            'email' => 'owner@email.com',
            'phone' => '5555551234'
        ]);

        User::factory()->role('doctor')->create([
            'name' => 'Doctor',
            'email' => 'doctor@email.com',
            'phone' => '5555551234'
        ]);
    }
}
