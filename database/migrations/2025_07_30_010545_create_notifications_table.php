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
            $table->unsignedTinyInteger('sender_id')->nullable();
            $table->enum('sender_role', ['Admin', 'Enforcer', 'System'])->nullable();
            $table->enum('target_role', ['Admin', 'Enforcer', 'Violator']);
            
            $table->unsignedInteger('violator_id')->nullable();
            $table->unsignedInteger('transaction_id')->nullable();

            $table->string('title', 100);
            $table->text('message');
            $table->enum('type', ['info', 'alert', 'reminder', 'warning']);
            $table->timestamps();
            $table->timestamp('read_at')->nullable();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('violator_id')->references('id')->on('violators')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
