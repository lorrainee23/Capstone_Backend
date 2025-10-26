<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update transactions that have "GPS Location" or empty location to have more meaningful names
        DB::table('transactions')
            ->where(function($query) {
                $query->where('location', 'GPS Location')
                      ->orWhere('location', '')
                      ->orWhereNull('location');
            })
            ->whereNotNull('gps_latitude')
            ->whereNotNull('gps_longitude')
            ->update([
                'location' => DB::raw("CONCAT('Location at ', ROUND(gps_latitude, 4), ', ', ROUND(gps_longitude, 4))")
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not easily reversible
        // You would need to manually restore the original location values
    }
};

