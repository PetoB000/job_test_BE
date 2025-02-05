<?php

namespace App\GraphQL;

use App\GraphQL\Resolvers\GalleryResolver;
use App\GraphQL\Resolvers\OrderResolver;
use App\GraphQL\Resolvers\ProductResolver;
use App\GraphQL\Resolvers\CategoryResolver;
use App\GraphQL\Resolvers\AttributeResolver;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\InputObjectType;

class Types
{
    private static $mutation;
    private static $query;
    private static $productType;
    private static $categoryType;
    private static $orderType;
    private static $orderItemType;
    private static $attributeInputType;
    private static $attributeItemInputType;
    private static $selectedAttributeInputType;
    private static $orderItemInputType;

    private static function attributeItemInputType(): InputObjectType
    {
        return self::$attributeItemInputType ?: (self::$attributeItemInputType = new InputObjectType([
            'name' => 'AttributeItemInput',
            'fields' => [
                'value' => Type::nonNull(Type::string()),
                'displayValue' => Type::nonNull(Type::string())
            ]
        ]));
    }

    private static function attributeInputType(): InputObjectType
    {
        return self::$attributeInputType ?: (self::$attributeInputType = new InputObjectType([
            'name' => 'AttributeInput',
            'fields' => [
                'name' => Type::nonNull(Type::string()),
                'type' => Type::nonNull(Type::string()),
                'items' => Type::listOf(self::attributeItemInputType())
            ]
        ]));
    }

    private static function selectedAttributeInputType(): InputObjectType
    {
        return self::$selectedAttributeInputType ?: (self::$selectedAttributeInputType = new InputObjectType([
            'name' => 'SelectedAttributeInput',
            'fields' => [
                'name' => Type::nonNull(Type::string()),
                'attribute_id' => Type::nonNull(Type::id())
            ]
        ]));
    }

    private static function orderItemInputType(): InputObjectType
    {
        return self::$orderItemInputType ?: (self::$orderItemInputType = new InputObjectType([
            'name' => 'OrderItemInput',
            'fields' => [
                'product_id' => Type::nonNull(Type::id()),
                'quantity' => Type::nonNull(Type::int()),
                'selected_attributes' => Type::listOf(self::selectedAttributeInputType())
            ]
        ]));
    }

    public static function query(): ObjectType
    {
        return self::$query ?: (self::$query = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'products' => [
                    'type' => Type::listOf(self::productType()),
                    'resolve' => function () {
                        $resolver = new ProductResolver();
                        return $resolver->resolveProducts();
                    }
                ],
                'product' => [
                    'type' => self::productType(),
                    'args' => [
                        'id' => Type::nonNull(Type::string())
                    ],
                    'resolve' => function ($root, $args) {
                        $resolver = new ProductResolver();
                        return $resolver->resolveProductById($args['id']);
                    }
                ],
                'categories' => [
                    'type' => Type::listOf(self::categoryType()),
                    'resolve' => function () {
                        $resolver = new CategoryResolver();
                        return $resolver->resolveCategories();
                    }
                ],
                'category' => [
                    'type' => self::categoryType(),
                    'args' => [
                        'name' => Type::nonNull(Type::string())
                    ],
                    'resolve' => function ($root, $args) {
                        $resolver = new CategoryResolver();
                        return $resolver->resolveCategoryByName($args['name']);
                    }
                ],
                'orders' => [
                    'type' => Type::listOf(self::orderType()),
                    'resolve' => function () {
                        $resolver = new OrderResolver();
                        return $resolver->resolveOrders();
                    }
                ],
                'order' => [
                    'type' => self::orderType(),
                    'args' => [
                        'id' => Type::nonNull(Type::string())
                    ],
                    'resolve' => function ($root, $args) {
                        $resolver = new OrderResolver();
                        return $resolver->resolveOrderById($args['id']);
                    }
                ]
            ]
        ]));
    }

    private static function productType(): ObjectType
    {
        return self::$productType ?: (self::$productType = new ObjectType([
            'name' => 'Product',
            'fields' => fn() => [
                'id' => Type::id(),
                'name' => Type::string(),
                'description' => Type::string(),
                'brand' => Type::string(),
                'in_stock' => Type::boolean(),
                'category' => [
                    'type' => self::categoryType(),
                    'resolve' => function ($product) {
                        $resolver = new CategoryResolver();
                        return $resolver->resolveCategoryById($product['category']);
                    }
                ],
                'price' => [
                    'type' => Type::float(),
                    'resolve' => function ($product) {
                        $resolver = new ProductResolver();
                        return $resolver->resolveProductPrice($product['id']);
                    }
                ],
                'gallery' => [
                    'type' => Type::listOf(Type::string()),
                    'resolve' => function ($product) {
                        $resolver = new GalleryResolver();
                        return $resolver->resolveGallery($product['id']);
                    }
                ],
                'attributes' => [
                    'type' => Type::listOf(new ObjectType([
                        'name' => 'AttributeSet',
                        'fields' => [
                            'id' => Type::id(),
                            'name' => Type::string(),
                            'type' => Type::string(),
                            'items' => [
                                'type' => Type::listOf(new ObjectType([
                                    'name' => 'AttributeItem',
                                    'fields' => [
                                        'id' => Type::id(),
                                        'displayValue' => Type::string(),
                                        'value' => Type::string()
                                    ]
                                ])),
                                'resolve' => function ($attribute) {
                                    $resolver = new AttributeResolver();
                                    return $resolver->resolveAttributeItems($attribute['id']);
                                }
                            ]
                        ]
                    ])),
                    'resolve' => function ($product) {
                        $resolver = new AttributeResolver();
                        return $resolver->resolveAttributes($product['id']);
                    }
                ]
            ]
        ]));
    }

    private static function categoryType(): ObjectType
    {
        return self::$categoryType ?: (self::$categoryType = new ObjectType([
            'name' => 'Category',
            'fields' => [
                'id' => Type::id(),
                'name' => Type::string(),
                'products' => [
                    'type' => Type::listOf(self::productType()),
                    'resolve' => function ($category) {
                        $resolver = new ProductResolver();
                        return $resolver->resolveProductsByCategory($category['id']);
                    }
                ]
            ]
        ]));
    }

    private static function orderType(): ObjectType
    {
        return self::$orderType ?: (self::$orderType = new ObjectType([
            'name' => 'Order',
            'fields' => [
                'id' => Type::id(),
                'customer_name' => Type::string(),
                'customer_email' => Type::string(),
                'status' => Type::string(),
                'created_at' => Type::string(),
                'items' => [
                    'type' => Type::listOf(self::orderItemType()),
                    'resolve' => function($order) {
                        $resolver = new OrderResolver();
                        return $resolver->resolveOrderItems($order['id']);
                    }
                ]
            ]
        ]));
    }

    private static function orderItemType(): ObjectType
    {
        return self::$orderItemType ?: (self::$orderItemType = new ObjectType([
            'name' => 'OrderItem',
            'fields' => [
                'id' => Type::id(),
                'product_id' => Type::id(),
                'quantity' => Type::int(),
                'price' => Type::float(),
                'product' => [
                    'type' => self::productType(),
                    'resolve' => function($orderItem) {
                        $resolver = new ProductResolver();
                        return $resolver->resolveProductById($orderItem['product_id']);
                    }
                ],
                'selected_attributes' => [
                    'type' => Type::listOf(new ObjectType([
                        'name' => 'OrderItemAttribute',
                        'fields' => [
                            'id' => Type::id(),
                            'name' => Type::string(),
                            'attribute_id' => Type::string()
                        ]
                    ])),
                    'resolve' => function($orderItem) {
                        $resolver = new OrderResolver();
                        return $resolver->resolveOrderItemAttributes($orderItem['id']);
                    }
                ]
            ]
        ]));
    }

    public static function mutation(): ObjectType
    {
        return self::$mutation ?: (self::$mutation = new ObjectType([
            'name' => 'Mutation',
            'fields' => [
                'createOrder' => [
                    'type' => self::orderType(),
                    'args' => [
                        'customer_name' => Type::nonNull(Type::string()),
                        'customer_email' => Type::nonNull(Type::string()),
                        'items' => Type::nonNull(Type::listOf(Type::nonNull(self::orderItemInputType())))
                    ],
                    'resolve' => function ($root, $args) {
                        $resolver = new OrderResolver();
                        return $resolver->createOrder($args);
                    }
                ],
                'createProduct' => [
                    'type' => self::productType(),
                    'args' => [
                        'name' => Type::nonNull(Type::string()),
                        'description' => Type::nonNull(Type::string()),
                        'brand' => Type::nonNull(Type::string()),
                        'category' => Type::nonNull(Type::string()),
                        'in_stock' => Type::nonNull(Type::boolean()),
                        'price' => Type::nonNull(Type::float()),
                        'gallery' => Type::listOf(Type::string()),
                        'attributes' => Type::listOf(self::attributeInputType())
                    ],
                    'resolve' => function ($root, $args) {
                        $resolver = new ProductResolver();
                        return $resolver->createProduct($args);
                    }
                ],
                'updateProduct' => [
                    'type' => self::productType(),
                    'args' => [
                        'id' => Type::nonNull(Type::id()),
                        'name' => Type::nonNull(Type::string()),
                        'description' => Type::nonNull(Type::string()),
                        'brand' => Type::nonNull(Type::string()),
                        'category' => Type::nonNull(Type::string()),
                        'in_stock' => Type::nonNull(Type::boolean()),
                        'price' => Type::nonNull(Type::float()),
                        'gallery' => Type::listOf(Type::string()),
                        'attributes' => Type::listOf(self::attributeInputType())
                    ],
                    'resolve' => function ($root, $args) {
                        $resolver = new ProductResolver();
                        return $resolver->updateProduct($args);
                    }
                ],
                'deleteProduct' => [
                    'type' => Type::boolean(),
                    'args' => [
                        'id' => Type::nonNull(Type::id())
                    ],
                    'resolve' => function ($root, $args) {
                        $resolver = new ProductResolver();
                        return $resolver->deleteProduct($args['id']);
                    }
                ],
                'createCategory' => [
                    'type' => self::categoryType(),
                    'args' => [
                        'name' => Type::nonNull(Type::string()),
                        'description' => Type::string(),
                        '__typename' => Type::string()
                    ],
                    'resolve' => function ($root, $args) {
                        $resolver = new CategoryResolver();
                        return $resolver->createCategory($args);
                    }
                ],
                'deleteCategory' => [
                    'type' => Type::boolean(),
                    'args' => [
                        'id' => Type::nonNull(Type::id())
                    ],
                    'resolve' => function ($root, $args) {
                        $resolver = new CategoryResolver();
                        return $resolver->deleteCategory($args['id']);
                    }
                ]
            ]
        ]));
    }
}
