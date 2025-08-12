<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Violator;
use App\Models\Violation;
use App\Models\User;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get active enforcers
        $enforcers = User::where('role', 'Enforcer')->where('status', 'active')->pluck('id')->toArray();
        
        // Get all violators
        $violators = Violator::pluck('id')->toArray();
        
        // Get all violations
        $violations = Violation::pluck('id')->toArray();
        
        // Vehicle types
        $vehicleTypes = ['Motor', 'Van', 'Motorcycle'];
        
        // Locations
        $locations = [
            'Main Street, Echague',
            'Rizal Avenue, Echague',
            'Bonifacio Street, Echague',
            'Mabini Street, Echague',
            'Aguinaldo Street, Echague',
            'Quezon Street, Echague',
            'Luna Street, Echague',
            'Roxas Street, Echague'
        ];

        // Create 50 random transactions for the last 30 days
        for ($i = 0; $i < 50; $i++) {
            $randomViolator = $violators[array_rand($violators)];
            $randomViolation = $violations[array_rand($violations)];
            $randomEnforcer = $enforcers[array_rand($enforcers)];
            $randomVehicleType = $vehicleTypes[array_rand($vehicleTypes)];
            $randomLocation = $locations[array_rand($locations)];
            
            // Random date within last 30 days
            $randomDate = now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            
            // Random status (80% pending, 20% paid)
            $status = rand(1, 10) <= 8 ? 'Pending' : 'Paid';
            
            Transaction::create([
                'violators_id' => $randomViolator,
                'violations_id' => $randomViolation,
                'apprehending_officer' => $randomEnforcer,
                'status' => $status,
                'location' => $randomLocation,
                'date_time' => $randomDate,
                'fine_amount' => Violation::find($randomViolation)->fine_amount,
                'vehicle_type' => $randomVehicleType,
                'receipt' => $status === 'Paid' ? 'sample_receipt.jpg' : null
            ]);
        }

        // Create additional transactions for repeat offenders
        $repeatOffenders = [1, 2, 3]; // First 3 violators will be repeat offenders
        
        foreach ($repeatOffenders as $violatorId) {
            // Add 2-4 more violations for each repeat offender
            $additionalViolations = rand(2, 4);
            
            for ($j = 0; $j < $additionalViolations; $j++) {
                $randomViolation = $violations[array_rand($violations)];
                $randomEnforcer = $enforcers[array_rand($enforcers)];
                $randomVehicleType = $vehicleTypes[array_rand($vehicleTypes)];
                $randomLocation = $locations[array_rand($locations)];
                
                // Random date within last 60 days
                $randomDate = now()->subDays(rand(0, 60))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
                
                // Random status
                $status = rand(1, 10) <= 6 ? 'Pending' : 'Paid';
                
                Transaction::create([
                    'violators_id' => $violatorId,
                    'violations_id' => $randomViolation,
                    'apprehending_officer' => $randomEnforcer,
                    'status' => $status,
                    'location' => $randomLocation,
                    'date_time' => $randomDate,
                    'fine_amount' => Violation::find($randomViolation)->fine_amount,
                    'vehicle_type' => $randomVehicleType,
                    'receipt' => $status === 'Paid' ? 'sample_receipt.jpg' : null
                ]);
            }
        }
    }
} 