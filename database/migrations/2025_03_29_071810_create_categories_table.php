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
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary(); 
            $table->string('name'); 
            $table->text('description')->nullable();

            $table->uuid('parent_id')->nullable(); 
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('set null'); 
            $table->uuid('created_by')->nullable(); 
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null'); 
            
            $table->enum('status', ['Active', 'Inactive'])->default('Active'); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
