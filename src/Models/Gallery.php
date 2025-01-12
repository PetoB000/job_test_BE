<?php

namespace App\Models;

/**
 * Gallery Model
 * 
 * Handles gallery operations and media management
 */
class Gallery extends AbstractModel
{
    /**
     * Get all gallery items
     * 
     * @return array List of all gallery items
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM gallery");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get gallery item by ID
     * 
     * @param string $id Gallery item identifier
     * @return array|null Gallery item data or null if not found
     */
    public function getById($id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM gallery WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get gallery items by product ID
     * 
     * @param string $productId Product identifier
     * @return array List of gallery URLs for the product
     */
    public function getByProductId($productId): array
    {
        $stmt = $this->db->prepare("SELECT url FROM gallery WHERE product_id = :product_id");
        $stmt->execute(['product_id' => $productId]);
        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'url');
    }
}
