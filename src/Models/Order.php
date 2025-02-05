<?php

namespace App\Models;

use PDO;

/**
 * Order Model
 * 
 * Handles all order-related database operations including
 * creation, retrieval, updates and deletions
 */
class Order extends AbstractCatalogItem
{
    /**
     * Creates a new order with items
     * 
     * @param array $data Order data including customer info and items
     * @return array Created order with items
     * @throws \Exception When product not found or transaction fails
     */
    public function create(array $data): array
    {
        $orderId = uniqid('order_', true);

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO orders (id, customer_name, customer_email, status)
                VALUES (:id, :customer_name, :customer_email, 'pending')
            ");

            $stmt->execute([
                'id' => $orderId,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email']
            ]);

            foreach ($data['items'] as $item) {
                $itemId = uniqid('item_', true);
                $price = $this->getProductPrice($item['product_id']);

                $stmt = $this->db->prepare("
                    INSERT INTO order_items (id, order_id, product_id, quantity, price)
                    VALUES (:id, :order_id, :product_id, :quantity, :price)
                ");

                $stmt->execute([
                    'id' => $itemId,
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $price
                ]);

                if (isset($item['selected_attributes'])) {
                    foreach ($item['selected_attributes'] as $attr) {
                        $stmt = $this->db->prepare("
                            INSERT INTO order_item_attributes (order_item_id, name, attribute_id)
                            VALUES (:order_item_id, :name, :attribute_id)
                        ");

                        $stmt->execute([
                            'order_item_id' => $itemId,
                            'name' => $attr['name'],
                            'attribute_id' => $attr['attribute_id']
                        ]);
                    }
                }
            }

            $this->db->commit();
            return $this->getById($orderId);
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT id, customer_name, customer_email, status, created_at
            FROM orders
            ORDER BY created_at DESC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderItems(string $orderId): array
    {
        $stmt = $this->db->prepare("
            SELECT id, order_id, product_id, quantity, price
            FROM order_items
            WHERE order_id = :order_id
        ");
        $stmt->execute(['order_id' => $orderId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderItemAttributes(string $orderItemId): array
    {
        $stmt = $this->db->prepare("
            SELECT id, order_item_id, name, attribute_id
            FROM order_item_attributes
            WHERE order_item_id = :order_item_id
        ");
        $stmt->execute(['order_item_id' => $orderItemId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id): array
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $order['items'] = $this->getOrderItems($id);
            foreach ($order['items'] as &$item) {
                $item['selected_attributes'] = $this->getOrderItemAttributes($item['id']);
            }
        }

        return $order;
    }

    /**
     * Updates order information
     * 
     * @param string $id Order identifier
     * @param array $data Updated order data
     * @return bool Success status
     */
    public function update($id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE orders
            SET customer_name = :customer_name,
                customer_email = :customer_email,
                status = :status
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'status' => $data['status']
        ]);
    }

    /**
     * Deletes an order and its items
     * 
     * @param string $id Order identifier
     * @return bool Success status
     * @throws \Exception When deletion fails
     */
    public function delete($id): bool
    {
        $this->db->beginTransaction();

        try {
            // Delete order items first
            $stmt = $this->db->prepare("DELETE FROM order_items WHERE order_id = :order_id");
            $stmt->execute(['order_id' => $id]);

            // Delete the order
            $stmt = $this->db->prepare("DELETE FROM orders WHERE id = :id");
            $stmt->execute(['id' => $id]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function getProductPrice(string $productId): float
    {
        $productModel = new Product();
        return $productModel->getPrice($productId);
    }
}
