<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\DTO\CategoryDTO;


class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(Request $request)
    {
        $query = Category::query();
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by parent_id if provided
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }
        
        $categories = $query->get();
        
        return response()->json([
            'data' => $categories,
            'message' => 'Categories retrieved successfully'
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'nullable|in:Active,Inactive',
        ]);

        $category = Category::create([
            'id' => Str::uuid(),
            'name' => $validatedData['name'],
            'description' => $validatedData['description'] ?? null,
            'parent_id' => $validatedData['parent_id'] ?? null,
            'created_by' => $request->user()->id,
            'status' => $validatedData['status'] ?? 'Active',
        ]);

        return response()->json([
            'data' => $category,
            'message' => 'Category created successfully'
        ], 201);
    }

    /**
     * Display the specified category.
     */
    public function show($id)
    {
        $category = Category::findOrFail($id);
        
        return response()->json([
            'data' => $category,
            'message' => 'Category retrieved successfully'
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'nullable|in:Active,Inactive',
        ]);

        $category->update($validatedData);

        return response()->json([
            'data' => $category,
            'message' => 'Category updated successfully'
        ]);
    }

    /**
     * Remove the specified category.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }
}