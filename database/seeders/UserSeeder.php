<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Deputy;
use App\Models\Head;
use App\Models\Enforcer;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1 Admin
        Admin::create([
            'id' => 1,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'username' => 'admin01',
            'email' => 'admin@moms.com',
            'office'=> 'POSU',
            'password' => Hash::make('password123'),
            'status' => 'active'
        ]);

        // 1 Head
        Head::create([
            'id' => 1,
            'first_name' => 'Head',
            'last_name' => 'User',
            'username' => 'head01',
            'email' => 'head@moms.com',
            'office'=> 'POSU',
            'password' => Hash::make('password123'),
            'status' => 'active'
        ]);
         Head::create([
            'id' => 2,
            'first_name' => 'John',
            'last_name' => 'Ray',
            'username' => 'john02',
            'email' => 'john@moms.com',
            'office'=> 'POSU',
            'password' => Hash::make('password123'),
            'status' => 'active'
        ]);


        // 1 Deputy
        Deputy::create([
            'id' => 1,
            'first_name' => 'Deputy',
            'last_name' => 'User',
            'username' => 'deputy01',
            'email' => 'deputy@moms.com',
            'office'=> 'POSU',
            'password' => Hash::make('password123'),
            'status' => 'active'
        ]);

        // 3 Enforcers
        Enforcer::create([
            'id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'enforcer01',
            'office' => 'POSU',
            'email' => 'enforcer1@moms.com',
            'password' => Hash::make('password123'),
            'status' => 'active'
        ]);

        Enforcer::create([
            'id' => 2,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'username' => 'enforcer02',
            'office' => 'PNP ISmart',
            'email' => 'enforcer2@moms.com',
            'password' => Hash::make('password123'),
            'status' => 'active'
        ]);

        Enforcer::create([
            'id' => 3,
            'first_name' => 'Mike',
            'last_name' => 'Johnson',
            'username' => 'enforcer03',
            'office' => 'PNP ISmart',
            'email' => 'enforcer3@moms.com',
            'password' => Hash::make('password123'),
            'status' => 'deactivate'
        ]);
    }
}
