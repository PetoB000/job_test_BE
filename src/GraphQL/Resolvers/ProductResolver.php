<?php

namespace App\GraphQL\Resolvers;

use App\Models\Product;

class ProductResolver
{
    private $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    public function resolveProducts()
    {
        return $this->productModel->getAll();
    }

    public function resolveProductById($id)
    {
        return $this->productModel->getById($id);
    }

    public function resolveProductsByCategory($categoryId)
    {
        return $this->productModel->getByCategoryId($categoryId);
    }

    public function resolveProductPrice($productId)
    {
        return $this->productModel->getPrice($productId);
    }
}
