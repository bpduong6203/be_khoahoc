<?php

namespace App\Services;

use App\Models\Category;
use App\DTO\CategoryDTO;
use Illuminate\Support\Str;

class CategoryService
{
    public function getCategories(array $filters = [])
    {
        $query = Category::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        return $query->get()->map(fn($category) => CategoryDTO::fromCategory($category));
    }

    public function createCategory(array $data, $userId)
    {
        $category = Category::create([
            'id' => Str::uuid(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'created_by' => $userId,
            'status' => $data['status'] ?? 'Active',
        ]);

        return CategoryDTO::fromCategory($category);
    }

    public function getCategoryById($id)
    {
        $category = Category::findOrFail($id);
        return CategoryDTO::fromCategory($category);
    }

    public function updateCategory($id, array $data)
    {
        $category = Category::findOrFail($id);
        $category->update($data);
        return CategoryDTO::fromCategory($category);
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
    }
}