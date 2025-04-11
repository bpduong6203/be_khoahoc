<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Lesson;
use App\DTO\MaterialDTO;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaterialService
{
    /**
     * Create a new material
     */
    public function createMaterial(array $data)
    {
        // Validate lesson existence
        $lesson = Lesson::find($data['lesson_id']);
        if (!$lesson) {
            throw new ModelNotFoundException('Lesson not found.');
        }

        // Store file if provided
        if (isset($data['file']) && $data['file']) {
            $fileName = time() . '_' . $data['file']->getClientOriginalName();
            $fileSize = $data['file']->getSize();
            $fileType = $data['file']->getClientMimeType();
            $filePath = $data['file']->storeAs('materials', $fileName, 'public');
            
            $data['file_url'] = $filePath;
            $data['file_type'] = $fileType;
            $data['file_size'] = round($fileSize / 1024); // Convert to KB
        }

        // Remove the file object from data
        if (isset($data['file'])) {
            unset($data['file']);
        }

        // Create the material
        $material = Material::create([
            'id' => Str::uuid(),
            'lesson_id' => $data['lesson_id'],
            'title' => $data['title'],
            'file_url' => $data['file_url'] ?? null,
            'file_type' => $data['file_type'] ?? null,
            'file_size' => $data['file_size'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        return MaterialDTO::fromMaterial($material);
    }

    /**
     * Get materials by lesson ID
     */
    public function getMaterialsByLessonId($lessonId)
    {
        $lesson = Lesson::find($lessonId);
        if (!$lesson) {
            throw new ModelNotFoundException('Lesson not found.');
        }

        $materials = Material::where('lesson_id', $lessonId)->get();
        return $materials->map(fn($material) => MaterialDTO::fromMaterial($material));
    }

    /**
     * Get material by ID
     */
    public function getMaterialById($id)
    {
        $material = Material::find($id);
        if (!$material) {
            throw new ModelNotFoundException('Material not found.');
        }

        return MaterialDTO::fromMaterial($material);
    }

    /**
     * Update material
     */
    
     public function updateMaterial($id, array $data)
{
    // Kiểm tra material tồn tại
    $material = Material::find($id);
    if (!$material) {
        throw new ModelNotFoundException('Material not found.');
    }
    
    Log::info("Updating material. ID: {$id}", ['data_keys' => array_keys($data)]);
    
    try {
        // Xử lý file nếu có
        if (isset($data['file']) && $data['file']) {
            // Xóa file cũ nếu có
            if ($material->file_url) {
                Storage::disk('public')->delete($material->file_url);
            }
            
            $fileName = time() . '_' . $data['file']->getClientOriginalName();
            $fileType = $data['file']->getClientMimeType();
            $fileSize = round($data['file']->getSize() / 1024);
            $filePath = $data['file']->storeAs('materials', $fileName, 'public');
            
            $material->file_url = $filePath;
            $material->file_type = $fileType;
            $material->file_size = $fileSize;
            
            Log::info("File updated for material {$id}: {$filePath}");
        }
        
        // Cập nhật các trường text
        if (isset($data['title'])) {
            $material->title = $data['title'];
        }
        
        if (isset($data['description'])) {
            $material->description = $data['description'];
        }
        
        if (isset($data['lesson_id'])) {
            $material->lesson_id = $data['lesson_id'];
        }
        
        // Lưu thay đổi
        $saved = $material->save();
        Log::info("Material save result for {$id}: " . ($saved ? "Success" : "Failed"));
        
        if (!$saved) {
            // Thử với SQL trực tiếp nếu save không thành công
            $updateData = [];
            
            if (isset($data['title'])) {
                $updateData['title'] = $data['title'];
            }
            
            if (isset($data['description'])) {
                $updateData['description'] = $data['description'];
            }
            
            if (isset($data['lesson_id'])) {
                $updateData['lesson_id'] = $data['lesson_id'];
            }
            
            if (isset($data['file']) && $data['file']) {
                $updateData['file_url'] = $filePath;
                $updateData['file_type'] = $fileType;
                $updateData['file_size'] = $fileSize;
            }
            
            $updateData['updated_at'] = now();
            
            $affected = DB::table('materials')
                ->where('id', $id)
                ->update($updateData);
                
            Log::info("Direct update result for {$id}: Affected rows = {$affected}");
        }
        
        // Refresh model và trả về
        $material = Material::find($id);
        return MaterialDTO::fromMaterial($material);
        
    } catch (\Exception $e) {
        Log::error("Error updating material {$id}: " . $e->getMessage());
        throw $e;
    }
}
    /**
     * Delete material
     */
    public function deleteMaterial($id)
    {
        $material = Material::find($id);
        if (!$material) {
            throw new ModelNotFoundException('Material not found.');
        }

        // Delete the file from storage
        if ($material->file_url) {
            Storage::disk('public')->delete($material->file_url);
        }

        $material->delete();
        return true;
    }
}