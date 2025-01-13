<?php

require __DIR__ . '/../vendor/autoload.php';

// CORS Headers
header('Access-Control-Allow-Origin: https://petob000.github.io');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only try to load .env file if it exists (local development)
if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    // Health check route
    $r->get('/health', 'healthCheck');
    // GraphQL route
    $r->addRoute(['POST', 'OPTIONS'], '/graphql', [App\Controllers\GraphQL::class, 'handle']);
});

function healthCheck() {
    header('Content-Type: application/json');
    return json_encode([
        'status' => 'ok',
        'database' => checkDatabaseConnection()
    ]);
}

// Add this helper function
function checkDatabaseConnection() {
    try {
        $dsn = "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'];
        $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return 'connected';
    } catch (\Throwable $e) {
        return $e->getMessage();
    }
}
$routeInfo = $dispatcher->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        header('HTTP/1.1 404 Not Found');
        echo '404 Not Found';
        break;

    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        header('HTTP/1.1 405 Method Not Allowed');
        echo '405 Method Not Allowed';
        break;

    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        
        if (is_string($handler) && function_exists($handler)) {
            echo $handler($vars);
        } else if (is_array($handler)) {
            echo $handler[0]::{$handler[1]}($vars);
        }
        break;
}