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
        Schema::create('progress', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('enrollment_id');
            $table->uuid('lesson_id');

            $table->foreign('enrollment_id')->references('id')->on('enrollments')->onDelete('cascade');
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade');

            $table->enum('status', ['Not Started', 'In Progress', 'Completed'])->default('Not Started');
            $table->datetime('start_date')->nullable();
            $table->datetime('completion_date')->nullable();
            $table->datetime('last_access_date')->nullable();
            $table->integer('time_spent')->nullable()->comment('Time spent in seconds');
            $table->timestamps();
            $table->unique(['enrollment_id', 'lesson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress');
    }
};
