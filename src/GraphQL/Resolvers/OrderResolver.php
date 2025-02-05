<?php

namespace App\GraphQL\Resolvers;

use App\Models\Order;

class OrderResolver
{
    private $orderModel;

    public function __construct()
    {
        $this->orderModel = new Order();
    }
    public function resolveOrders(): array
    {
        return $this->orderModel->getAll();
    }

    public function resolveOrderItems(string $orderId): array
    {
        return $this->orderModel->getOrderItems($orderId);
    }

    public function resolveOrderItemAttributes(string $orderItemId): array
    {
        return $this->orderModel->getOrderItemAttributes($orderItemId);
    }

    public function resolveOrderById(string $orderId): array 
    {
        return $this->orderModel->getById($orderId);
    }


    public function createOrder(array $args): array
    {
        try {
            $result = $this->orderModel->create($args);
            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
