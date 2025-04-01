<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    public function index()
    {
        $students = User::whereHas('role', function ($query) {
            $query->where('name', 'student');
        })->get();
        return response()->json($students);
    }

    public function store(Request $request)
    {
        try {
            $studentRole = Role::where('name', 'student')->first();

            if (!$studentRole) {
                return response()->json(['error' => 'Không tìm thấy vai trò student'], 500);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
            ]);

            $student = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role_id' => $studentRole->id,
            ]);

            return response()->json([
                'message' => 'Thêm student thành công!',
                'student' => $student
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi tạo student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(User $user)
    {
        if ($user->role->name !== 'student') {
            return response()->json(['error' => 'Đây không phải là student'], 403);
        }
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        if ($user->role->name !== 'student') {
            return response()->json(['error' => 'Đây không phải là student'], 403);
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
            'message' => 'Cập nhật student thành công!',
            'student' => $user
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->role->name !== 'student') {
            return response()->json(['error' => 'Đây không phải là student'], 403);
        }

        $user->delete();
        return response()->json(['message' => 'Xóa student thành công!']);
    }
}
