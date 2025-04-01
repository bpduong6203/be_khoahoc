<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // GET /api/roles
    public function index()
    {
        return response()->json(Role::all());
    }

    // POST /api/roles
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        $role = Role::create($validated);
        return response()->json($role, 201);
    }

    // GET /api/roles/{id}
    public function show($id)
    {
        $role = Role::findOrFail($id);
        return response()->json($role);
    }

    // PUT /api/roles/{id}
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $id,
        ]);

        $role->update($validated);
        return response()->json($role);
    }

    // DELETE /api/roles/{id}
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
