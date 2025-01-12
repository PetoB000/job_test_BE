<?php

namespace App\Models;

/**
 * Category Model
 * 
 * Handles category operations and management
 */
class Category extends AbstractModel
{
    /**
     * Get all categories
     * 
     * @return array List of all categories
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM categories");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get category by ID
     * 
     * @param string $id Category identifier
     * @return array|null Category data or null if not found
     */
    public function getById($id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
