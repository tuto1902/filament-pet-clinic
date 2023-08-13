<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClinicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Clinic::factory()
            ->hasAttached(User::factory()->create([
                'name' => 'Admin',
                'email' => 'admin@email.com',
                'role_id' => Role::whereName('admin')->first()->id,
                'phone' => '5555551234'
            ]))
            ->create([
                'name' => 'Default Clinic'
            ]);
    }
}
