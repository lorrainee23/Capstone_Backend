<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Violation;

class ViolationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $violations = [
            [
                'id' => 1,
                'name' => 'No Helmet',
                'description' => 'Driving a motorcycle without wearing a helmet',
                'fine_amount' => 1000.00
            ],
            [
                'id' => 2,
                'name' => 'No License',
                'description' => 'Driving without a valid driver\'s license',
                'fine_amount' => 1000.00
            ],
            [
                'id' => 3,
                'name' => 'No Registration',
                'description' => 'Driving an unregistered vehicle',
                'fine_amount' => 1000.00
            ],
            [
                'id' => 4,
                'name' => 'Reckless Driving',
                'description' => 'Driving in a manner that endangers other road users',
                'fine_amount' => 1000.00
            ],
            [
                'id' => 5,
                'name' => 'Overloading',
                'description' => 'Carrying passengers or cargo beyond vehicle capacity',
                'fine_amount' => 1000.00
            ],
            [
                'id' => 6,
                'name' => 'No Seatbelt',
                'description' => 'Driving without wearing a seatbelt',
                'fine_amount' => 1000.00
            ],
            [
                'id' => 7,
                'name' => 'Illegal Parking',
                'description' => 'Parking in a prohibited area or blocking traffic',
                'fine_amount' => 1000.00
            ],
            [
                'id' => 8,
                'name' => 'Beating the Red Light',
                'description' => 'Proceeding through a red traffic light',
                'fine_amount' => 1000.00
            ],
            [
                'id' => 9,
                'name' => 'Overspeeding',
                'description' => 'Driving above the speed limit',
                'fine_amount' => 1000.00
            ],
            [
                'id' => 10,
                'name' => 'Drunk Driving',
                'description' => 'Driving under the influence of alcohol',
                'fine_amount' => 1000.00
            ],
            [
                'id' => 11,
                'name' => 'No Insurance',
                'description' => 'Driving without valid vehicle insurance',
                'fine_amount' => 1000.00
            ],
            [
                'id' => 12,
                'name' => 'Illegal U-turn',
                'description' => 'Making a U-turn in a prohibited area',
                'fine_amount' => 1000.00
            ],
            [
                'id' => 13,
                'name' => 'No Brake Light',
                'description' => 'Driving with non-functional brake lights',
                'fine_amount' => 1000.00
            ],
            [
                'id' => 14,
                'name' => 'No Horn',
                'description' => 'Driving without a functional horn',
                'fine_amount' => 1000.00
            ],
            [
                'id' => 15,
                'name' => 'No Side Mirror',
                'description' => 'Driving without proper side mirrors',
                'fine_amount' => 1000.00
            ]
        ];

        foreach ($violations as $violation) {
            Violation::create($violation);
        }
    }
} 