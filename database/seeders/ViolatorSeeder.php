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
        // Generate specific test locations with exact violation counts
        $testLocations = [
            [
                'name' => 'Echague Poblacion Road',
                'lat' => 16.721955,
                'lng' => 121.685299,
                'violation_count' => 51
            ],
            [
                'name' => 'Savemore Market',
                'lat' => 16.705410004969956,
                'lng' => 121.67657061323274,
                'violation_count' => 101
            ],
            [
                'name' => 'Echague Police Station',
                'lat' => 16.715707547619218,
                'lng' => 121.68292386994924,
                'violation_count' => 21
            ]
        ];

        // Create test data for specific locations
        foreach ($testLocations as $testLocation) {
            echo "Creating {$testLocation['violation_count']} violations for {$testLocation['name']}...\n";
            
            // Create a violator for this location
            $violator = Violator::create([
                'first_name' => 'Test',
                'middle_name' => 'Heatmap',
                'last_name' => 'User',
                'mobile_number' => '09' . rand(100000000, 999999999),
                'gender' => rand(0, 1),
                'professional' => rand(0, 1),
                'license_number' => 'TEST' . rand(100000000, 999999999),
                'barangay' => 'Test Barangay',
                'city' => 'Echague',
                'province' => 'Isabela',
                'password' => bcrypt('password123'),
            ]);

            // Create a vehicle for this violator
            $vehicle = Vehicle::create([
                'violators_id' => $violator->id,
                'owner_first_name' => 'Test',
                'owner_middle_name' => 'Heatmap',
                'owner_last_name' => 'User',
                'make' => 'Honda',
                'model' => 'Test Model',
                'vehicle_type' => 'Motorcycle',
                'owner_barangay' => 'Test Barangay',
                'owner_city' => 'Echague',
                'owner_province' => 'Isabela',
                'plate_number' => 'TEST' . rand(1000, 9999),
                'color' => 'Red',
            ]);

            // Create multiple transactions for this location
            for ($i = 0; $i < $testLocation['violation_count']; $i++) {
                $violation = Violation::inRandomOrder()->first();
                $officerId = rand(1, 3);

                // Create transactions over the past 6 months
                $transactionDate = Carbon::now()
                    ->subMonths(rand(0, 6))
                    ->subDays(rand(0, 30))
                    ->subHours(rand(8, 17))
                    ->subMinutes(rand(0, 59));

                Transaction::create([
                    'violator_id' => $violator->id,
                    'vehicle_id' => $vehicle->id,
                    'violation_id' => $violation->id,
                    'apprehending_officer' => $officerId,
                    'status' => 'Pending',
                    'location' => $testLocation['name'],
                    'gps_latitude' => $testLocation['lat'],
                    'gps_longitude' => $testLocation['lng'],
                    'date_time' => $transactionDate,
                    'fine_amount' => $violation->fine_amount,
                ]);
            }
            
            echo "✅ Created {$testLocation['violation_count']} violations for {$testLocation['name']}\n";
        }

    }
}
