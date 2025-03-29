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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('content')->nullable();
            $table->enum('type', ['System', 'Course', 'Payment', 'Message']);
            $table->integer('reference_id')->nullable()->comment('ID of the related entity');
            $table->string('reference_type', 50)->nullable()->comment('Type of the related entity');
            $table->boolean('is_read')->default(false);
            $table->enum('status', ['Active', 'Archived', 'Deleted'])->default('Active');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
