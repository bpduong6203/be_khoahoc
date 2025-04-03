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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('user_id');
            $table->uuid('course_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');

            $table->datetime('expiry_date')->nullable();
            $table->enum('payment_status', ['Pending', 'Completed', 'Failed', 'Refunded'])->default('Pending');
            $table->enum('payment_method', ['Momo', 'Bank', 'Paypal', 'Cash'])->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->decimal('price', 10, 2);
            $table->enum('status', ['Pending','Active', 'Completed', 'Cancelled'])->default('Pending');
            $table->datetime('completion_date')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
