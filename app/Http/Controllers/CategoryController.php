<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CategoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'parent_id']);
        $categories = $this->categoryService->getCategories($filters);

        return response()->json([
            'data' => $categories,
            'message' => 'Categories retrieved successfully',
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'nullable|in:Active,Inactive',
        ]);

        $category = $this->categoryService->createCategory($validatedData, $request->user()->id);

        return response()->json([
            'data' => $category,
            'message' => 'Category created successfully',
        ], 201);
    }

    public function show($id)
    {
        try {
            $category = $this->categoryService->getCategoryById($id);
            return response()->json([
                'data' => $category,
                'message' => 'Category retrieved successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Category not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'nullable|in:Active,Inactive',
        ]);

        try {
            $category = $this->categoryService->updateCategory($id, $validatedData);
            return response()->json([
                'data' => $category,
                'message' => 'Category updated successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Category not found'], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $this->categoryService->deleteCategory($id);
            return response()->json(['message' => 'Category deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Category not found'], 404);
        }
    }
}