<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('notification_id');
            $table->unsignedInteger('user_id');
            $table->string('user_role');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('notification_id')
                ->references('id')->on('notifications')
                ->onDelete('cascade');

            $table->index(['user_role', 'user_id']);
            $table->unique(['notification_id', 'user_id', 'user_role'], 'notification_reads_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_reads');
    }
};


