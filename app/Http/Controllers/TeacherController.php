<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    public function index()
    {
        $teachers = User::whereHas('role', function ($query) {
            $query->where('name', 'teacher');
        })->get();
        return response()->json($teachers);
    }

    public function store(Request $request)
    {
        try {
            $teacherRole = Role::where('name', 'teacher')->first();

            if (!$teacherRole) {
                return response()->json(['error' => 'Không tìm thấy vai trò teacher'], 500);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
            ]);

            $teacher = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role_id' => $teacherRole->id,
            ]);

            return response()->json([
                'message' => 'Thêm teacher thành công!',
                'teacher' => $teacher
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi tạo teacher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(User $user)
    {
        if ($user->role->name !== 'teacher') {
            return response()->json(['error' => 'Đây không phải là teacher'], 403);
        }
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        if ($user->role->name !== 'teacher') {
            return response()->json(['error' => 'Đây không phải là teacher'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($request->only('name', 'email'));

        if ($request->filled('password')) {
            $user->update(['password' => bcrypt($request->password)]);
        }

        return response()->json([
            'message' => 'Cập nhật teacher thành công!',
            'teacher' => $user
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->role->name !== 'teacher') {
            return response()->json(['error' => 'Đây không phải là teacher'], 403);
        }

        $user->delete();
        return response()->json(['message' => 'Xóa teacher thành công!']);
    }
}
