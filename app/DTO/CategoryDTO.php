<?php

namespace App\DTO;

use App\Models\Category;

class CategoryDTO
{
    public $id;
    public $name;
    public $description;
    public $parent_id;
    public $status;

    public static function fromCategory(Category $category)
    {
        $dto = new self();
        $dto->id = $category->id;
        $dto->name = $category->name;
        $dto->description = $category->description;
        $dto->parent_id = $category->parent_id;
        $dto->status = $category->status;
        
        return $dto;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'status' => $this->status,
        ];
    }
}