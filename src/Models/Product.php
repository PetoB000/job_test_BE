<?php

namespace App\Models;


use Ramsey\Uuid\Uuid;

/**
 * Product Model
 *
 * Handles core product operations and pricing
 */
class Product extends AbstractModel
{
    /**
     * Get all products
     * @return array
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM products");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get product by ID
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
     * Get products by category ID
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
     * Get product price
     * @param string $productId
     * @return float
     */
    public function getPrice($productId): float
    {
        $stmt = $this->db->prepare("SELECT amount FROM prices WHERE product_id = :product_id LIMIT 1");
        $stmt->execute(['product_id' => $productId]);
        return (float)$stmt->fetchColumn();
    }

    /**
     * Create new product
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        $this->db->beginTransaction();
        try {
            // Generate UUID
            $productId = Uuid::uuid4()->toString();

            // Insert main product first
            $stmt = $this->db->prepare("
            INSERT INTO products (id, name, description, category_id, brand, in_stock, __typename)
            VALUES (:id, :name, :description, :category_id, :brand, :in_stock, :__typename)
        ");
            $stmt->execute([
                'id' => $productId,
                'name' => $data['name'],
                'description' => $data['description'],
                'category_id' => $data['category'],
                'brand' => $data['brand'],
                'in_stock' => $data['in_stock'],
                '__typename' => 'Product'
            ]);

            // Get next price ID
            $maxPriceIdQuery = $this->db->query("SELECT MAX(id) FROM prices");
            $priceId = (int)$maxPriceIdQuery->fetchColumn() + 1;

            // Insert price with explicit ID
            $stmt = $this->db->prepare("
            INSERT INTO prices (id, amount, currency, product_id, __typename)
            VALUES (:id, :amount, 'USD', :product_id, :__typename)
        ");
            $stmt->execute([
                'id' => $priceId,
                'amount' => $data['price'],
                'product_id' => $productId,
                '__typename' => 'Price'
            ]);

            // Handle gallery images
            if (!empty($data['gallery'])) {
                $maxGalleryIdQuery = $this->db->query("SELECT MAX(id) FROM gallery");
                $galleryId = (int)$maxGalleryIdQuery->fetchColumn() + 1;

                foreach ($data['gallery'] as $url) {
                    $stmt = $this->db->prepare("
                    INSERT INTO gallery (id, product_id, url, __typename)
                    VALUES (:id, :product_id, :url, :__typename)
                ");
                    $stmt->execute([
                        'id' => $galleryId++,
                        'product_id' => $productId,
                        'url' => $url,
                        '__typename' => 'GalleryImage'
                    ]);
                }
            }

            // Handle attributes
            if (!empty($data['attributes'])) {
                $maxAttributeIdQuery = $this->db->query("SELECT MAX(id) FROM attributes");
                $attributeId = (int)$maxAttributeIdQuery->fetchColumn() + 1;

                foreach ($data['attributes'] as $attribute) {
                    $stmt = $this->db->prepare("
                    INSERT INTO attributes (id, name, type, product_id, __typename)
                    VALUES (:id, :name, :type, :product_id, :__typename)
                ");
                    $stmt->execute([
                        'id' => $attributeId,
                        'name' => $attribute['name'],
                        'type' => $attribute['type'],
                        'product_id' => $productId,
                        '__typename' => 'AttributeSet'
                    ]);

                    $maxItemIdQuery = $this->db->query("SELECT MAX(id) FROM attribute_items");
                    $itemId = (int)$maxItemIdQuery->fetchColumn() + 1;

                    foreach ($attribute['items'] as $item) {
                        $stmt = $this->db->prepare("
                        INSERT INTO attribute_items (id, attribute_id, value, display_value, __typename)
                        VALUES (:id, :attribute_id, :value, :display_value, :__typename)
                    ");
                        $stmt->execute([
                            'id' => $itemId++,
                            'attribute_id' => $attributeId,
                            'value' => $item['value'],
                            'display_value' => $item['displayValue'],
                            '__typename' => 'AttributeItem'
                        ]);
                    }
                    $attributeId++;
                }
            }

            $this->db->commit();
            return $this->getById($productId);
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }


    /**
     * Update product
     * @param string $id
     * @param array $data
     * @return array
     */
    public function update(string $id, array $data): array
    {
        $this->db->beginTransaction();
        try {
            // First delete related attribute items and attributes
            $stmt = $this->db->prepare("
            DELETE ai FROM attribute_items ai
            JOIN attributes a ON ai.attribute_id = a.id
            WHERE a.product_id = :product_id
        ");
            $stmt->execute(['product_id' => $id]);

            $stmt = $this->db->prepare("
            DELETE FROM attributes 
            WHERE product_id = :product_id
        ");
            $stmt->execute(['product_id' => $id]);

            // Handle price update
            if (isset($data['price'])) {
                $stmt = $this->db->prepare("
                UPDATE prices
                SET amount = :amount
                WHERE product_id = :product_id
            ");
                $stmt->execute([
                    'amount' => $data['price'],
                    'product_id' => $id
                ]);
            }

            // Update product fields
            $updateData = array_filter([
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null,
                'brand' => $data['brand'] ?? null,
                'category_id' => $data['category'] ?? null,
                'in_stock' => $data['in_stock'] ?? null
            ]);

            if (!empty($updateData)) {
                $setClauses = [];
                $params = ['id' => $id];

                foreach ($updateData as $key => $value) {
                    if ($value !== null) {
                        $setClauses[] = "$key = :$key";
                        $params[$key] = $value;
                    }
                }

                $sql = "UPDATE products SET " . implode(', ', $setClauses) . " WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            }

            $this->db->commit();
            return $this->getById($id);
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    /**
     * Delete product
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        $this->db->beginTransaction();
        try {
            // Delete attribute items first
            $stmt = $this->db->prepare("
            DELETE ai FROM attribute_items ai
            JOIN attributes a ON ai.attribute_id = a.id
            WHERE a.product_id = :product_id
        ");
            $stmt->execute(['product_id' => $id]);

            // Delete attributes
            $stmt = $this->db->prepare("DELETE FROM attributes WHERE product_id = :product_id");
            $stmt->execute(['product_id' => $id]);

            // Delete gallery images
            $stmt = $this->db->prepare("DELETE FROM gallery WHERE product_id = :product_id");
            $stmt->execute(['product_id' => $id]);

            // Delete prices
            $stmt = $this->db->prepare("DELETE FROM prices WHERE product_id = :product_id");
            $stmt->execute(['product_id' => $id]);

            // Finally delete the product
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
            $stmt->execute(['id' => $id]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
