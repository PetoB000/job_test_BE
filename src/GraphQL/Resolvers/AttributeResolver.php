<?php

namespace App\GraphQL\Resolvers;

use App\Models\ProductAttribute;

class AttributeResolver
{
    private $attributeModel;

    public function __construct()
    {
        $this->attributeModel = new ProductAttribute();
    }

    public function resolveAttributes($productId)
    {
        return $this->attributeModel->getProductAttributes($productId);
    }

    public function resolveAttributesByType(int $productId, string $type): array
    {
        $attributes = $this->attributeModel->getProductAttributes($productId);
        return array_filter($attributes, fn($attr) => $attr['type'] === $type);
    }

    public function resolveAttributeItems($attributeId)
    {
        return $this->attributeModel->getAttributeItems($attributeId);
    }
}
