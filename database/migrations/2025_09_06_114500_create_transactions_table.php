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
            $table->unsignedInteger('ticket_number')->unique();
            $table->unsignedInteger('violator_id');
            $table->unsignedTinyInteger('violation_id');
            $table->unsignedTinyInteger('apprehending_officer');
            $table->unsignedInteger('vehicle_id');
            
            $table->enum('status', ['Pending', 'Paid', 'For Court'])->default('Pending');
            
            $table->timestamp('warning_sent_at')->nullable();
            $table->timestamp('court_filed_at')->nullable();

            $table->string('location', 100)->nullable();
            $table->dateTime('date_time');
            $table->decimal('fine_amount', 10, 2);
            $table->string('receipt', 255)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('violation_id')->references('id')->on('violations')->onDelete('cascade');
            $table->foreign('apprehending_officer')->references('id')->on('enforcers')->onDelete('cascade');
            $table->foreign('violator_id')->references('id')->on('violators')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
