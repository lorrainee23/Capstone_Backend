<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Violator;
use App\Models\Vehicle;
use App\Models\Transaction;
use App\Models\Violation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ViolatorSeeder extends Seeder
{
    public function run(): void
    {
        // Dates range
        $dates = [];
for ($d = strtotime('2025-08-28'); $d <= strtotime('2025-09-05'); $d = strtotime("+1 day", $d)) {
    $hour = rand(8, 17);
    $minute = rand(0, 59);
    $second = rand(0, 59);
    
    $dates[] = date("Y-m-d H:i:s", mktime($hour, $minute, $second, date("m", $d), date("d", $d), date("Y", $d)));
}


        $violatorNames = [
            ['Juan', 'Dela', 'Cruz', true],
            ['Maria', 'Santos', 'Garcia', false],
            ['Pedro', 'Reyes', 'Martinez', true],
            ['Ana', 'Lopez', 'Rodriguez', false],
            ['Carlos', 'Gonzalez', 'Perez', true],
            ['Isabella', 'Fernandez', 'Torres', false],
            ['Miguel', 'Ramirez', 'Flores', true],
            ['Sofia', 'Morales', 'Vargas', false],
            ['Luis', 'Castro', 'Diaz', true],
            ['Camila', 'Ramos', 'Ortega', false],
            ['Daniel', 'Mendoza', 'Navarro', true],
            ['Emma', 'Torres', 'Santos', false],
            ['Rafael', 'Reyes', 'Gutierrez', true],
            ['Gabriela', 'Lopez', 'Cabrera', false],
            ['Antonio', 'Sanchez', 'Martinez', true],
            ['Victoria', 'Delgado', 'Morales', false],
            ['Fernando', 'Garcia', 'Perez', true],
            ['Mariana', 'Diaz', 'Ramos', false],
            ['Ricardo', 'Vargas', 'Santos', true],
            ['Elena', 'Cruz', 'Fernandez', false],
        ];

        $plateLetters = ['ABC','DEF','GHI','JKL','MNO','PQR','STU','VWX','YZA','BCD'];
        $vehicleModels = ['Honda Wave 125','Yamaha Mio 125','Suzuki Raider 150','Kawasaki Rouser 200','Honda Click 160','Yamaha NMAX 155','Suzuki Burgman 200','Kymco Like 150'];

        foreach ($violatorNames as $index => $name) {
            DB::beginTransaction();
            try {
                $mobile_number = '09' . rand(100000000, 999999999);
                $license_number = strtoupper(Str::random(2)) . rand(10,99) . '-' . rand(10,99) . '-' . rand(100000,999999);

                $violator = Violator::create([
                    'first_name' => $name[0],
                    'middle_name' => $name[1],
                    'last_name' => $name[2],
                    'email' => strtolower($name[0]) . $index . '@example.com',
                    'mobile_number' => $mobile_number,
                    'gender' => $name[3],
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
                    'owner_first_name' => $name[0],
                    'owner_middle_name' => $name[1],
                    'owner_last_name' => $name[2],
                    'make' => 'Honda',
                    'model' => $vehicleModels[array_rand($vehicleModels)],
                    'vehicle_type' => ['Motor','Van','Motorcycle','Truck','Bus'][rand(0,4)],
                    'owner_barangay' => 'Barangay San Jose',
                    'owner_city' => 'Echague',
                    'owner_province' => 'Isabela',
                    'plate_number' => $plate_number,
                ]);

                $violation = Violation::inRandomOrder()->first();
                $officerId = rand(1,3);
                $transactionDate = $dates[array_rand($dates)];

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
                echo "Failed for violator {$name[0]}: {$e->getMessage()}\n";
            }
        }
    }
}
