<?php

namespace App\Models;

/**
 * Abstract Product Base Class
 * 
 * Provides common product functionality and data structure
 */
abstract class AbstractProduct extends AbstractCatalogItem
{
    /**
     * Get the product type identifier
     * 
     * @return string Product type
     */
    abstract public function getType(): string;

    /**
     * Load product attributes
     * 
     * @return array Product attributes
     */
    abstract protected function loadAttributes(): array;

    /**
     * Validate product stock status
     * 
     * @return bool Stock availability status
     */
    abstract protected function validateStock(): bool;

    /**
     * Set product data
     * 
     * @param array $data Product data
     * @return self
     */
    public function setData(array $data)
    {
        $this->data = $data;
        $this->id = $data['id'] ?? null;
        return $this;
    }

    /**
     * Get formatted product data
     * 
     * @return array Structured product data
     */
    public function getProductData(): array
    {
        return [
            'id' => $this->data['id'] ?? null,
            'name' => $this->data['name'] ?? null,
            'description' => $this->data['description'] ?? null,
            'brand' => $this->data['brand'] ?? null,
            'in_stock' => $this->validateStock(),
            'attributes' => $this->loadAttributes(),
            '__typename' => $this->getType()
        ];
    }
}
