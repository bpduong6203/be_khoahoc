<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    public function index()
    {
        $users = User::whereHas('role', function ($query) {
            $query->whereIn('name', ['student', 'teacher']);
        })->get();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        try {
            $allowedRoleIds = Role::whereIn('name', ['student', 'teacher'])->pluck('id')->toArray();

            if (empty($allowedRoleIds)) {
                return response()->json(['error' => 'Không tìm thấy vai trò student hoặc teacher'], 500);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'role_id' => 'required|exists:roles,id|in:' . implode(',', $allowedRoleIds),
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role_id' => $request->role_id,
            ]);

            return response()->json([
                'message' => 'Thêm mới thành công!',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi tạo user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(User $user)
    {
        if (!in_array($user->role->name, ['student', 'teacher'])) {
            return response()->json(['error' => 'Bạn chỉ có thể xem student hoặc teacher'], 403);
        }
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        if (!in_array($user->role->name, ['student', 'teacher'])) {
            return response()->json(['error' => 'Bạn chỉ có thể chỉnh sửa student hoặc teacher'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id|in:' . implode(',', Role::whereIn('name', ['student', 'teacher'])->pluck('id')->toArray()),
        ]);

        $user->update($request->only('name', 'email', 'role_id'));

        if ($request->filled('password')) {
            $user->update(['password' => bcrypt($request->password)]);
        }

        return response()->json([
            'message' => 'Cập nhật thành công!',
            'user' => $user
        ]);
    }

    public function destroy(User $user)
    {
        if (!in_array($user->role->name, ['student', 'teacher'])) {
            return response()->json(['error' => 'Bạn chỉ có thể xóa student hoặc teacher'], 403);
        }

        $user->delete();
        return response()->json(['message' => 'Xóa thành công!']);
    }
}
