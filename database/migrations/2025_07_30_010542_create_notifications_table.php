<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');   
            $table->unsignedTinyInteger('sender_id');
            $table->enum('sender_role', ['Admin', 'Enforcer']);
            $table->enum('target_role', ['Admin', 'Enforcer', 'Violator']);
            $table->string('title', 100);
            $table->text('message');
            $table->enum('type', ['info', 'alert', 'reminder', 'warning']);
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
