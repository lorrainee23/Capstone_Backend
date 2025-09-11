<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->tinyIncrements('id'); // tiny_int(3)
            $table->string('first_name', 255);
            $table->string('middle_name', 255)->nullable();
            $table->string('last_name', 255);
            $table->string('email', 255)->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('username', 255);
            $table->string('password', 255);
            $table->string('image', 255)->nullable();
            $table->enum('status', ['active', 'inactive', 'deactivate'])->default('active');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
