<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::create('users', function (Blueprint $table) {
			$table->id();
			$table->string('email', 255)->unique()->notNullable();
			$table->string('password', 255)->notNullable();
			$table->string('name', 255)->notNullable();
			$table->enum('role', ['administrator', 'manager', 'user'])->default('user');
			$table->boolean('active')->default(true);
			$table->timestamp('created_at')->useCurrent();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('users');
	}
};
