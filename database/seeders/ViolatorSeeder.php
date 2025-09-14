<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Violator;
use App\Models\Vehicle;
use App\Models\Transaction;
use App\Models\Violation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ViolatorSeeder extends Seeder
{
    public function run(): void
    {
        // Generate 25 violators
        $totalViolators = 8;

        // Random first names/last names pool
        $firstNames = ['Juan','Maria','Pedro','Ana','Carlos','Jose','Luisa','Miguel','Rosa','Diego'];
        $lastNames  = ['Dela Cruz','Santos','Reyes','Lopez','Garcia','Martinez','Rodriguez','Gonzalez','Perez','Torres'];

        // Vehicle options
        $plateLetters = ['ABC','DEF','GHI','JKL','MNO','PQR','STU','VWX','YZA','BCD'];
        $vehicleModels = [
            'Honda Wave 125','Yamaha Mio 125','Suzuki Raider 150',
            'Kawasaki Rouser 200','Honda Click 160','Yamaha NMAX 155',
            'Suzuki Burgman 200','Kymco Like 150'
        ];

        for ($i = 0; $i < $totalViolators; $i++) {
            DB::beginTransaction();
            try {
                $first = $firstNames[array_rand($firstNames)];
                $last  = $lastNames[array_rand($lastNames)];
                $middle = Str::random(5);
                $email = strtolower($first) . $i . '@example.com';

                $mobile_number = '09' . rand(100000000, 999999999);
                $license_number = strtoupper(Str::random(2)) . rand(10,99) . '-' . rand(10,99) . '-' . rand(100000,999999);

                $violator = Violator::create([
                    'first_name' => $first,
                    'middle_name' => $middle,
                    'last_name' => $last,
                    'email' => $email,
                    'mobile_number' => $mobile_number,
                    'gender' => rand(0,1), // true/false
                    'professional' => rand(0,1),
                    'license_number' => $license_number,
                    'barangay' => 'Barangay San Jose',
                    'city' => 'Echague',
                    'province' => 'Isabela',
                    'id_photo' => null,
                    'password' => bcrypt('password123'),
                ]);

                $plate_number = $plateLetters[array_rand($plateLetters)] . rand(1000,9999);
                $vehicle = Vehicle::create([
                    'violators_id' => $violator->id,
                    'owner_first_name' => $first,
                    'owner_middle_name' => $middle,
                    'owner_last_name' => $last,
                    'make' => 'Honda',
                    'model' => $vehicleModels[array_rand($vehicleModels)],
                    'vehicle_type' => ['Motor','Van','Motorcycle','Truck','Bus'][rand(0,4)],
                    'owner_barangay' => 'Barangay San Jose',
                    'owner_city' => 'Echague',
                    'owner_province' => 'Isabela',
                    'plate_number' => $plate_number,
                    'color' => ['Red','Blue','Black','White','Gray','Green'][rand(0,5)], 
                ]);

                $violation = Violation::inRandomOrder()->first();
                $officerId = rand(1,3);

                // Assign dates across 2023, 2024, and 2025
                $year = [2023, 2024, 2025][array_rand([2023, 2024, 2025])];
                $month = rand(1, 12);
                $day = rand(1, 28); // safe day
                $hour = rand(8, 17);
                $minute = rand(0, 59);
                $second = rand(0, 59);

                $transactionDate = Carbon::create($year, $month, $day, $hour, $minute, $second);

                Transaction::create([
                    'violator_id' => $violator->id,
                    'vehicle_id' => $vehicle->id,
                    'violation_id' => $violation->id,
                    'apprehending_officer' => $officerId,
                    'status' => 'Pending',
                    'location' => 'Barangay San Jose',
                    'date_time' => $transactionDate,
                    'fine_amount' => $violation->fine_amount,
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                echo "Failed for violator {$i}: {$e->getMessage()}\n";
            }
        }
    }
}
