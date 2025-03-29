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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('phone_number', 15)->unique()->nullable();
            $table->string('password');
            $table->string('full_name');
            $table->string('avatar')->nullable();
            $table->boolean('is_email_verified')->default(false);
            $table->boolean('is_2fa_enabled')->default(false);
            $table->enum('social_login_type', ['Facebook', 'Google', 'Github', 'None'])->default('None');
            $table->string('social_login_id', 100)->nullable();
            $table->enum('status', ['Active', 'Inactive', 'Blocked'])->default('Active');
            $table->timestamp('last_login')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
