<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('violators_id');
            $table->unsignedTinyInteger('violations_id');
            $table->unsignedTinyInteger('apprehending_officer');
            $table->enum('status', ['Pending', 'Paid'])->default('Pending');
            $table->string('location', 100);
            $table->dateTime('date_time');
            $table->decimal('fine_amount', 10, 2);
            $table->string('receipt', 255)->nullable();
            $table->enum('vehicle_type', ['Motor', 'Van', 'Motorcycle']);
            $table->timestamps();

            $table->foreign('violators_id')->references('id')->on('violators')->onDelete('cascade');
            $table->foreign('violations_id')->references('id')->on('violations')->onDelete('cascade');
            $table->foreign('apprehending_officer')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
