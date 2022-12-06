<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsultationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            
			$table->text('attribute')->nullable();
			$table->unsignedBigInteger('payment_type')->default(0);
            $table->datetime('evaluated_at');
            
			$table->unsignedBigInteger('patient_id')->default(0);
            $table->unsignedBigInteger('doctor_id')->default(0);
            
            
			$table->unsignedBigInteger('user_id')->default(0);
            $table->tinyInteger('status')->default('0');
			$table->softDeletes();
			$table->timestamps();

			$table->foreign('patient_id')
					->references('id')
					->on('patients')
					->onUpdate('cascade')
					->onDelete('cascade');

			$table->foreign('doctor_id')
					->references('id')
					->on('doctors')
					->onUpdate('cascade')
					->onDelete('cascade');

			$table->foreign('user_id')
					->references('id')
					->on('users')
					->onUpdate('no action')
					->onDelete('no action');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('consultations');
	}
}
