<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin user
        User::create([
            'id' => 1,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@moms.com',
            'password' => Hash::make('password123'),
            'role' => 'Admin',
            'status' => 'active'
        ]);

        // Create Enforcer users
        User::create([
            'id' => 2,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'enforcer1@moms.com',
            'password' => Hash::make('password123'),
            'role' => 'Enforcer',
            'status' => 'active'
        ]);

        User::create([
            'id' => 3,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'enforcer2@moms.com',
            'password' => Hash::make('password123'),
            'role' => 'Enforcer',
            'status' => 'active'
        ]);

        User::create([
            'id' => 4,
            'first_name' => 'Mike',
            'last_name' => 'Johnson',
            'email' => 'enforcer3@moms.com',
            'password' => Hash::make('password123'),
            'role' => 'Enforcer',
            'status' => 'inactive'
        ]);
    }
} 