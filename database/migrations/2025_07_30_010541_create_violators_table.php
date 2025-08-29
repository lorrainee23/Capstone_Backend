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
        Schema::create('violators', function (Blueprint $table) {
            $table->increments('id');

            $table->string('email', 255)->nullable();
            $table->string('password', 255)->nullable();
            $table->boolean('email_verified')->default(false);
            $table->string('first_name', 255);
            $table->string('middle_name', 255)->nullable();
            $table->string('last_name', 255);
            $table->char('mobile_number', 11);
            $table->boolean('gender');
            $table->char('license_number', 16)->unique();
            $table->string('barangay', 255);
            $table->string('city', 255);
            $table->string('province', 255);
            $table->boolean('professional');
            $table->string('id_photo', 255)->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violators');
    }
};
