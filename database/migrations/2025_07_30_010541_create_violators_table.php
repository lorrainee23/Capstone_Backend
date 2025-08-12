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
            $table->id();
            $table->tinyInteger('violator_user_id', false, true)->nullable();
            $table->string('email', 255);
            $table->string('password', 255);
            $table->boolean('email_verified')->default(false);
            $table->string('first_name', 255);
            $table->string('middle_name', 255)->nullable();
            $table->string('last_name', 255);
            $table->char('mobile_number', 11);
            $table->boolean('gender');
            $table->char('license_number', 16);
            $table->char('plate_number', 8);
            $table->string('model', 100);
            $table->string('id_photo', 255)->nullable();
            $table->string('address', 255);
            $table->timestamps();
            
            $table->foreign('violator_user_id')->references('id')->on('users')->onDelete('set null');
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
