<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Violator;

class ViolatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $violators = [
            [
                'email' => 'violator1@example.com',
                'password' => bcrypt('password123'),
                'email_verified' => true,
                'first_name' => 'Juan',
                'middle_name' => 'Dela',
                'last_name' => 'Cruz',
                'mobile_number' => '09123456789',
                'gender' => true,
                'license_number' => 'A01-12-345678',
                'plate_number' => 'ABC1234',
                'model' => 'Honda Wave 125',
                'address' => '123 Main Street, Echague, Isabela',
                'id_photo' => null
            ],
            [
                'email' => 'violator2@example.com',
                'password' => bcrypt('password123'),
                'email_verified' => true,
                'first_name' => 'Maria',
                'middle_name' => 'Santos',
                'last_name' => 'Garcia',
                'mobile_number' => '09234567890',
                'gender' => false,
                'license_number' => 'B02-23-456789',
                'plate_number' => 'XYZ5678',
                'model' => 'Yamaha Mio 125',
                'address' => '456 Oak Avenue, Echague, Isabela',
                'id_photo' => null
            ],
            [
                'email' => 'violator3@example.com',
                'password' => bcrypt('password123'),
                'email_verified' => true,
                'first_name' => 'Pedro',
                'middle_name' => 'Reyes',
                'last_name' => 'Martinez',
                'mobile_number' => '09345678901',
                'gender' => true,
                'license_number' => 'C03-34-567890',
                'plate_number' => 'DEF9012',
                'model' => 'Suzuki Raider 150',
                'address' => '789 Pine Road, Echague, Isabela',
                'id_photo' => null
            ],
            [
                'email' => 'violator4@example.com',
                'password' => bcrypt('password123'),
                'email_verified' => true,
                'first_name' => 'Ana',
                'middle_name' => 'Lopez',
                'last_name' => 'Rodriguez',
                'mobile_number' => '09456789012',
                'gender' => false,
                'license_number' => 'D04-45-678901',
                'plate_number' => 'GHI3456',
                'model' => 'Kawasaki Rouser 200',
                'address' => '321 Elm Street, Echague, Isabela',
                'id_photo' => null
            ],
            [
                'email' => 'violator5@example.com',
                'password' => bcrypt('password123'),
                'email_verified' => true,
                'first_name' => 'Carlos',
                'middle_name' => 'Gonzalez',
                'last_name' => 'Perez',
                'mobile_number' => '09567890123',
                'gender' => true,
                'license_number' => 'E05-56-789012',
                'plate_number' => 'JKL7890',
                'model' => 'Honda Click 160',
                'address' => '654 Maple Drive, Echague, Isabela',
                'id_photo' => null
            ],
            [
                'email' => 'violator6@example.com',
                'password' => bcrypt('password123'),
                'email_verified' => true,
                'first_name' => 'Isabella',
                'middle_name' => 'Fernandez',
                'last_name' => 'Torres',
                'mobile_number' => '09678901234',
                'gender' => false,
                'license_number' => 'F06-67-890123',
                'plate_number' => 'MNO1234',
                'model' => 'Yamaha NMAX 155',
                'address' => '987 Cedar Lane, Echague, Isabela',
                'id_photo' => null
            ],
            [
                'email' => 'violator7@example.com',
                'password' => bcrypt('password123'),
                'email_verified' => true,
                'first_name' => 'Miguel',
                'middle_name' => 'Ramirez',
                'last_name' => 'Flores',
                'mobile_number' => '09789012345',
                'gender' => true,
                'license_number' => 'G07-78-901234',
                'plate_number' => 'PQR5678',
                'model' => 'Suzuki Burgman 200',
                'address' => '147 Birch Court, Echague, Isabela',
                'id_photo' => null
            ],
            [
                'email' => 'violator8@example.com',
                'password' => bcrypt('password123'),
                'email_verified' => true,
                'first_name' => 'Sofia',
                'middle_name' => 'Morales',
                'last_name' => 'Vargas',
                'mobile_number' => '09890123456',
                'gender' => false,
                'license_number' => 'H08-89-012345',
                'plate_number' => 'STU9012',
                'model' => 'Kymco Like 150',
                'address' => '258 Spruce Way, Echague, Isabela',
                'id_photo' => null
            ]
        ];

        foreach ($violators as $violator) {
            Violator::create($violator);
        }
    }
} 