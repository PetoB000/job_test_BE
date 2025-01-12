<?php

namespace App\Models;

/**
 * Product Model
 * 
 * Handles core product operations and pricing
 */
class Product extends AbstractModel
{
    /**
     * @return array
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM products");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function getById($id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $categoryId
     * @return array
     */
    public function getByCategoryId($categoryId): array
    {
        $stmt = $this->db->prepare("
                SELECT * FROM products WHERE category_id = :category_id
                UNION
                SELECT * FROM products WHERE :category_id = 1
            ");
        $stmt->execute(['category_id' => $categoryId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $productId
     * @return float
     */
    public function getPrice($productId): float
    {
        $stmt = $this->db->prepare("SELECT amount FROM prices WHERE product_id = :product_id LIMIT 1");
        $stmt->execute(['product_id' => $productId]);
        return (float)$stmt->fetchColumn();
    }
}
