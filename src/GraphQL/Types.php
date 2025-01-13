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

/**
 * GraphQL Types Definition Class
 * 
 * Defines all GraphQL types, queries, and mutations for the API
 */
class Types
{
    /** @var ObjectType */
    private static $mutation;
    
    /** @var ObjectType */
    private static $query;
    
    /** @var ObjectType */
    private static $productType;
    
    /** @var ObjectType */
    private static $categoryType;
    
    /** @var ObjectType */
    private static $orderType;
    
    /** @var ObjectType */
    private static $orderItemType;

    /**
     * Defines available GraphQL queries
     * 
     * @return ObjectType Query type definition
     */
    public static function query(): ObjectType
    {
        return self::$query ?: (self::$query = new ObjectType([
            'name' => 'Query',
            'fields' => [
                // Products list query
                'products' => [
                    'type' => Type::listOf(self::productType()),
                    'resolve' => function () {
                        $resolver = new ProductResolver();
                        return $resolver->resolveProducts();
                    }
                ],
                // Single product query
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
                // Categories list query
                'categories' => [
                    'type' => Type::listOf(self::categoryType()),
                    'resolve' => function () {
                        $resolver = new CategoryResolver();
                        return $resolver->resolveCategories();
                    }
                ],
                // Single category query
                'category' => [
                    'type' => self::categoryType(),
                    'args' => [
                        'name' => Type::nonNull(Type::string())
                    ],
                    'resolve' => function ($root, $args) {
                        $resolver = new CategoryResolver();
                        return $resolver->resolveCategoryByName($args['name']);
                    }
                ]
            ]
        ]));
    }

    /**
     * Defines Product type with all its fields and resolvers
     * 
     * @return ObjectType Product type definition
     */
    private static function productType(): ObjectType
    {
        return self::$productType ?: (self::$productType = new ObjectType([
            'name' => 'Product',
            'fields' => [
                'id' => Type::id(),
                'name' => Type::string(),
                'description' => Type::string(),
                'brand' => Type::string(),
                'in_stock' => Type::boolean(),
                // Dynamic price resolution
                'price' => [
                    'type' => Type::float(),
                    'resolve' => function ($product) {
                        $resolver = new ProductResolver();
                        return $resolver->resolveProductPrice($product['id']);
                    }
                ],
                // Product gallery images
                'gallery' => [
                    'type' => Type::listOf(Type::string()),
                    'resolve' => function ($product) {
                        $resolver = new GalleryResolver();
                        return $resolver->resolveGallery($product['id']);
                    }
                ],
                // Product attributes with nested items
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

    /**
     * Defines Category type with its fields and resolvers
     * 
     * @return ObjectType Category type definition
     */
    private static function categoryType(): ObjectType
    {
        return self::$categoryType ?: (self::$categoryType = new ObjectType([
            'name' => 'Category',
            'fields' => [
                'id' => Type::id(),
                'name' => Type::string(),
                // Products in category
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

    /**
     * Defines Order type structure
     * 
     * @return ObjectType Order type definition
     */
    private static function orderType(): ObjectType
    {
        return self::$orderType ?: (self::$orderType = new ObjectType([
            'name' => 'Order',
            'fields' => [
                'id' => Type::id(),
                'customer_name' => Type::string(),
                'customer_email' => Type::string(),
                'total' => Type::float(),
                'status' => Type::string(),
                'items' => Type::listOf(self::orderItemType())
            ]
        ]));
    }
      /**
     * Defines OrderItem type structure
     * 
     * @return ObjectType OrderItem type definition
     */
      private static function orderItemType(): ObjectType
      {
          return self::$orderItemType ?: (self::$orderItemType = new ObjectType([
              'name' => 'OrderItem',
              'fields' => [
                  'product_id' => Type::id(),
                  'quantity' => Type::int(),
                  'price' => Type::float(),
                  'selected_attributes' => Type::listOf(new ObjectType([
                      'name' => 'SelectedAttribute',
                      'fields' => [
                          'name' => Type::string(),
                          'value' => Type::string(),
                          'display_value' => Type::string()
                      ]
                  ]))
              ]
          ]));
      }
    /**
     * Defines available GraphQL mutations
     * 
     * @return ObjectType Mutation type definition
     */
    public static function mutation(): ObjectType
    {
        return self::$mutation ?: (self::$mutation = new ObjectType([
            'name' => 'Mutation',
            'fields' => [
                // Create order mutation
                'createOrder' => [
                    'type' => self::orderType(),
                    'args' => [
                        'customer_name' => Type::nonNull(Type::string()),
                        'customer_email' => Type::nonNull(Type::string()),
                        'items' => Type::nonNull(Type::listOf(Type::nonNull(new InputObjectType([
                            'name' => 'OrderItemInput',
                            'fields' => [
                                'product_id' => Type::nonNull(Type::id()),
                                'quantity' => Type::nonNull(Type::int()),
                                'selected_attributes' => Type::listOf(new InputObjectType([
                                    'name' => 'SelectedAttributeInput',
                                    'fields' => [
                                        'name' => Type::nonNull(Type::string()),
                                        'attribute_id' => Type::nonNull(Type::id())
                                    ]
                                ]))
                            ]
                        ]))))
                    ],
                    'resolve' => function ($root, $args) {
                        $resolver = new OrderResolver();
                        return $resolver->createOrder($args);
                    }
                ]            ]
        ]));
    }
}