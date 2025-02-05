<?php

namespace App\GraphQL\Resolvers;

use App\Models\Category;

class CategoryResolver
{
    private $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
    }

    public function resolveCategories()
    {
        return $this->categoryModel->getAll();
    }

    public function resolveCategoryByName(string $name)
    {
        return $this->categoryModel->getByName($name);
    }
    
    public function createCategory($args)
    {
        return $this->categoryModel->create([
            'name' => $args['name'],
            'description' => $args['description'] ?? null
        ]);
    }

    public function deleteCategory(string $id): bool
    {
        return $this->categoryModel->delete($id);
    }

    public function getAllCategories()
    {
        return $this->categoryModel->getAll();
    }
    
    public function resolveCategoryById(string $categoryId): ?array
    {
        return $this->categoryModel->getById($categoryId);
    }
}
