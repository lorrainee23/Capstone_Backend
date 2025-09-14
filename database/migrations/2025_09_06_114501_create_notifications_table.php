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

            // Sender info
            $table->unsignedInteger('sender_id')->nullable();
            $table->enum('sender_role', ['Head','Deputy','Admin','Enforcer','System'])->nullable();
            $table->string('sender_name')->nullable();

            $table->unsignedInteger('target_id')->nullable();   
            $table->enum('target_type', ['Head','Deputy','Admin','Enforcer','Violator','All', 'Management'])->nullable();
 
            $table->unsignedInteger('violator_id')->nullable();
            $table->unsignedInteger('transaction_id')->nullable();

            $table->string('title', 100);
            $table->text('message');
            $table->enum('type', ['info', 'alert', 'reminder', 'warning']);
            $table->softDeletes();
            $table->timestamps();
            $table->timestamp('read_at')->nullable();
            
            $table->foreign('violator_id')
                  ->references('id')->on('violators')
                  ->onDelete('cascade');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
