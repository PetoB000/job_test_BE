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
     * @return array
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM categories");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get category by ID
     * @param string $id
     * @return array|null
     */
    public function getById($id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get category by name
     * @param string $name
     * @return array|null
     */
    public function getByName(string $name): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE name = :name");
        $stmt->execute(['name' => $name]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Create new category
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        $stmt = $this->db->prepare("
            INSERT INTO categories (name, __typename)
            VALUES (:name, :__typename)
        ");
        $stmt->execute([
            'name' => $data['name'],
            '__typename' => "Category"
        ]);

        return $this->getById($this->db->lastInsertId());
    }

    /**
     * Delete category
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        $this->db->beginTransaction();
        try {
            // First delete gallery images for all products in this category
            $stmt = $this->db->prepare("
                DELETE g FROM gallery g
                JOIN products p ON g.product_id = p.id
                WHERE p.category_id = :category_id
            ");
            $stmt->execute(['category_id' => $id]);

            // Delete attribute items for products in this category
            $stmt = $this->db->prepare("
                DELETE ai FROM attribute_items ai
                JOIN attributes a ON ai.attribute_id = a.id
                JOIN products p ON a.product_id = p.id
                WHERE p.category_id = :category_id
            ");
            $stmt->execute(['category_id' => $id]);

            // Delete attributes for products in this category
            $stmt = $this->db->prepare("
                DELETE a FROM attributes a
                JOIN products p ON a.product_id = p.id
                WHERE p.category_id = :category_id
            ");
            $stmt->execute(['category_id' => $id]);

            // Delete prices for products in this category
            $stmt = $this->db->prepare("
                DELETE pr FROM prices pr
                JOIN products p ON pr.product_id = p.id
                WHERE p.category_id = :category_id
            ");
            $stmt->execute(['category_id' => $id]);

            // Delete products in this category
            $stmt = $this->db->prepare("
                DELETE FROM products 
                WHERE category_id = :category_id
            ");
            $stmt->execute(['category_id' => $id]);

            // Finally delete the category
            $stmt = $this->db->prepare("
                DELETE FROM categories 
                WHERE id = :id
            ");
            $result = $stmt->execute(['id' => $id]);

            $this->db->commit();
            return $result;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
