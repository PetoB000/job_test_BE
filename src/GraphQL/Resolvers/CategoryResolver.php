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

    public function resolveCategoryById($id)
    {
        return $this->categoryModel->getById($id);
    }
}
