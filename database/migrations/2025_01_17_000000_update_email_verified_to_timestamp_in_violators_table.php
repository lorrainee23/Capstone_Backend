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
        Schema::table('violators', function (Blueprint $table) {
            // Drop the existing boolean column
            $table->dropColumn('email_verified');
        });
        
        Schema::table('violators', function (Blueprint $table) {
            // Add the new timestamp column
            $table->timestamp('email_verified_at')->nullable()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('violators', function (Blueprint $table) {
            // Drop the timestamp column
            $table->dropColumn('email_verified_at');
        });
        
        Schema::table('violators', function (Blueprint $table) {
            // Add back the boolean column
            $table->boolean('email_verified')->default(false)->after('password');
        });
    }
};
