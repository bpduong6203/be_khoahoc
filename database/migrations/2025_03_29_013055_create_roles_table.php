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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        // Thêm dữ liệu mẫu vào bảng roles
        DB::table('roles')->insert([
            ['name' => 'Admin', 'description' => 'Quản trị viên hệ thống, có toàn quyền', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Teacher', 'description' => 'Giảng viên có thể tạo và quản lý khóa học', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Student', 'description' => 'Học viên có thể đăng ký và học các khóa học', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
