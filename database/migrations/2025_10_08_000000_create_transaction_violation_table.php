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
		Schema::create('transaction_violation', function (Blueprint $table) {
			$table->unsignedInteger('transaction_id');
			$table->unsignedTinyInteger('violation_id');
			$table->timestamps();

			$table->primary(['transaction_id', 'violation_id']);
			$table->index('transaction_id');
			$table->index('violation_id');

			$table->foreign('transaction_id')
				->references('id')->on('transactions')
				->onDelete('cascade');
			$table->foreign('violation_id')
				->references('id')->on('violations')
				->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('transaction_violation');
	}
};


