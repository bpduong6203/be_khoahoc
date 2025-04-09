<?php

namespace App\Http\Controllers;

use App\Services\MaterialService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class MaterialController extends Controller
{
    protected $materialService;

    public function __construct(MaterialService $materialService)
    {
        $this->materialService = $materialService;
    }

    /**
     * Get all materials for a specific lesson
     */
    public function index(Request $request, $lessonId)
    {
        try {
            $materials = $this->materialService->getMaterialsByLessonId($lessonId);
            return response()->json([
                'data' => $materials,
                'message' => 'Materials retrieved successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving materials: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create a new material
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'lesson_id' => 'required|string|exists:lessons,id',
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240', // 10MB max
            'description' => 'nullable|string',
        ]);

        try {
            $material = $this->materialService->createMaterial($validatedData);
            return response()->json([
                'data' => $material,
                'message' => 'Material created successfully',
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating material: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get a specific material
     */
    public function show($id)
    {
        try {
            $material = $this->materialService->getMaterialById($id);
            return response()->json([
                'data' => $material,
                'message' => 'Material retrieved successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Material not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving material: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update a material
     */
    public function update(Request $request, $id)
{
    $validatedData = $request->validate([
        'lesson_id' => 'sometimes|string|exists:lessons,id',
        'title' => 'sometimes|string|max:255',
        'file' => 'sometimes|file|max:10240', // 10MB max
        'description' => 'nullable|string',
    ]);

    try {
        // Thêm log để kiểm tra dữ liệu nhận được
        Log::info("Update request for material {$id}:", [
            'has_file' => $request->hasFile('file'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'lesson_id' => $request->input('lesson_id')
        ]);

        $material = $this->materialService->updateMaterial($id, $validatedData);
        return response()->json([
            'data' => $material,
            'message' => 'Material updated successfully',
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json(['message' => 'Material not found'], 404);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Error updating material: ' . $e->getMessage()], 500);
    }
}
    /**
     * Delete a material
     */
    public function destroy($id)
    {
        try {
            $this->materialService->deleteMaterial($id);
            return response()->json(['message' => 'Material deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Material not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting material: ' . $e->getMessage()], 500);
        }
    }

}