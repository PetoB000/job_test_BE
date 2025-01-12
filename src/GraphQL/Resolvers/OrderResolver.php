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
