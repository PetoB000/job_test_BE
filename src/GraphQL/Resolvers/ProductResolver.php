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

    public function createProduct($args)
    {
        return $this->productModel->create([
            'name' => $args['name'],
            'description' => $args['description'],
            'brand' => $args['brand'],
            'category' => $args['category'],
            'in_stock' => $args['in_stock'],
            'price' => $args['price'],
            'gallery' => $args['gallery'] ?? [],
            'attributes' => $args['attributes'] ?? []
        ]);
    }

    public function updateProduct($args)
    {
        $updateData = [
            'name' => $args['name'],
            'description' => $args['description'],
            'brand' => $args['brand'],
            'category' => $args['category'],
            'in_stock' => $args['in_stock'],
            'price' => $args['price'],
            'gallery' => $args['gallery'] ?? [],
            'attributes' => $args['attributes'] ?? []
        ];

        return $this->productModel->update($args['id'], $updateData);
    }

    public function deleteProduct($id)
    {
        return $this->productModel->delete($id);
    }
}
