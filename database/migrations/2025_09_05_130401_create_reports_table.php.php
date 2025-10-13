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
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('report_content')->nullable();
            $table->json('summary')->nullable();        
            $table->json('files')->nullable();
            $table->string('generated_by_type')->nullable();
            $table->unsignedTinyInteger('generated_by_id')->nullable();
            $table->string('noted_by_name')->nullable();
            $table->string('prepared_by_name')->nullable();
            $table->softDeletes();          
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
