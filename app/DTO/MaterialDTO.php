<?php

namespace App\DTO;

use App\Models\Material;

class MaterialDTO
{
    public function __construct(
        public array $material
    ) {}

    public static function fromMaterial(Material $material)
    {
        return new self(
            material: [
                'id' => $material->id,
                'lesson_id' => $material->lesson_id,
                'title' => $material->title,
                'file_url' => $material->file_url,
                'file_type' => $material->file_type,
                'file_size' => $material->file_size,
                'description' => $material->description,
                'created_at' => $material->created_at,
                'updated_at' => $material->updated_at
            ]
        );
    }

    public function toArray(): array
    {
        return $this->material;
    }
}