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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->integer('duration')->nullable()->comment('Duration in minutes');
            $table->enum('level', ['Beginner', 'Intermediate', 'Advanced', 'All Levels'])->nullable();
            $table->text('requirements')->nullable();
            $table->text('objectives')->nullable();
            $table->enum('status', ['Draft', 'Published', 'Archived'])->default('Draft');
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('enrollment_count')->default(0);
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
