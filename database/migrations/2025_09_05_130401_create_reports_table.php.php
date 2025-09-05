<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->string('period');
            $table->json('report_content')->nullable();
            $table->json('summary')->nullable();        
            $table->json('files')->nullable();
            $table->softDeletes();          
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
