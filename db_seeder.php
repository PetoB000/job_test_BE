<?php
// Database connection
$host = 'localhost';
$db = 'product_catalog';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Load and decode JSON data
$json = file_get_contents('data.json');
$data = json_decode($json, true)['data'];

// Insert categories
$categories = $data['categories'];
foreach ($categories as $category) {
    $stmt = $pdo->prepare("INSERT INTO categories (name, __typename) VALUES (:name, :__typename)");
    $stmt->execute([
        'name' => $category['name'],
        '__typename' => $category['__typename']
    ]);
}

// Insert products
$products = $data['products'];
foreach ($products as $product) {
    $stmt = $pdo->prepare("
        INSERT INTO products (id, name, description, category_id, brand, in_stock, __typename) 
        VALUES (:id, :name, :description, (SELECT id FROM categories WHERE name = :category), :brand, :in_stock, :__typename)
    ");
    $stmt->execute([
        'id' => $product['id'],
        'name' => $product['name'],
        'description' => $product['description'],
        'category' => $product['category'],
        'brand' => $product['brand'],
        'in_stock' => $product['inStock'] ? 1 : 0,
        '__typename' => $product['__typename']
    ]);

    // Insert attributes
    foreach ($product['attributes'] as $attribute) {
        $stmt = $pdo->prepare("
            INSERT INTO attributes (name, type, product_id, __typename) 
            VALUES (:name, :type, :product_id, :__typename)
        ");
        $stmt->execute([
            'name' => $attribute['name'],
            'type' => $attribute['type'],
            'product_id' => $product['id'],
            '__typename' => $attribute['__typename']
        ]);
        $attributeId = $pdo->lastInsertId();

        // Insert attribute items
        foreach ($attribute['items'] as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO attribute_items (attribute_id, value, display_value, __typename) 
                VALUES (:attribute_id, :value, :display_value, :__typename)
            ");
            $stmt->execute([
                'attribute_id' => $attributeId,
                'value' => $item['value'],
                'display_value' => $item['displayValue'],
                '__typename' => $item['__typename']
            ]);
        }
    }

    // Insert prices
    foreach ($product['prices'] as $price) {
        $stmt = $pdo->prepare("
            INSERT INTO prices (amount, currency, product_id, __typename) 
            VALUES (:amount, :currency, :product_id, :__typename)
        ");
        $stmt->execute([
            'amount' => $price['amount'],
            'currency' => $price['currency']['label'],
            'product_id' => $product['id'],
            '__typename' => $price['__typename']
        ]);
    }

    // Insert gallery images
    foreach ($product['gallery'] as $url) {
        $stmt = $pdo->prepare("
            INSERT INTO gallery (product_id, url, __typename) 
            VALUES (:product_id, :url, :__typename)
        ");
        $stmt->execute([
            'product_id' => $product['id'],
            'url' => $url,
            '__typename' => 'GalleryImage'
        ]);
    }
}

echo "Database populated successfully!";

