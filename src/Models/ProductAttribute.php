<?php

namespace App\Models;
/**
 * Product Attribute Model
 * 
 * Handles product attribute operations and relationships
 */
class ProductAttribute extends AbstractModel
{
    /**
     * Get all attributes
     * 
     * @return array List of all attributes
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM attributes");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get attribute by ID
     * 
     * @param string $id Attribute identifier
     * @return array|null Attribute data or null if not found
     */
    public function getById($id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM attributes WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get attributes for a specific product
     * 
     * @param string $productId Product identifier
     * @return array List of product attributes
     */
    public function getProductAttributes($productId)
    {
        $stmt = $this->db->prepare("SELECT * FROM attributes WHERE product_id = :product_id");
        $stmt->execute(['product_id' => $productId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get attribute items for a specific attribute
     * 
     * @param string $attributeId Attribute identifier
     * @return array List of attribute items
     */
    public function getAttributeItems($attributeId)
    {
        $stmt = $this->db->prepare("SELECT id, value as displayValue, value FROM attribute_items WHERE attribute_id = :attribute_id");
        $stmt->execute(['attribute_id' => $attributeId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
