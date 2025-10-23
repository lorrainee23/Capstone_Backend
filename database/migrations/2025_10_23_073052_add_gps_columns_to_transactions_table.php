<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('gps_latitude', 10, 8)->nullable()->comment('GPS latitude coordinate');
            $table->decimal('gps_longitude', 11, 8)->nullable()->comment('GPS longitude coordinate');
            $table->decimal('gps_accuracy', 8, 2)->nullable()->comment('GPS accuracy in meters');
            $table->timestamp('gps_timestamp')->nullable()->comment('GPS capture timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['gps_latitude', 'gps_longitude', 'gps_accuracy', 'gps_timestamp']);
        });
    }
};
