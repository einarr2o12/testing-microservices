<?php
// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set content type to JSON
header('Content-Type: application/json');

// Function for logging
function logMessage($level, $message) {
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] [$level] $message");
}

// Parse URL to determine the endpoint
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/api/products';
$method = $_SERVER['REQUEST_METHOD'];

// Database configuration
$dbHost = getenv('DB_HOST') ?: 'mysql';
$dbName = getenv('DB_DATABASE') ?: 'product_db';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: 'password';

// Category service URL
$categoryServiceUrl = getenv('CATEGORY_SERVICE_URL') ?: 'http://category-service:3000';

logMessage('INFO', "Starting Product Service - DB: $dbHost, Category Service: $categoryServiceUrl");

// Connect to MySQL
try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    logMessage('INFO', "Database connection established");
} catch (PDOException $e) {
    logMessage('ERROR', "Database connection failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Create products table if it doesn't exist
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10, 2) NOT NULL,
            category_id VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    logMessage('INFO', "Products table created/verified");
} catch (PDOException $e) {
    logMessage('ERROR', "Failed to create products table: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create products table: ' . $e->getMessage()]);
    exit;
}

// Function to validate category
function validateCategory($categoryId) {
    global $categoryServiceUrl;
    
    logMessage('INFO', "Validating category ID: $categoryId");
    
    $ch = curl_init("$categoryServiceUrl/api/categories/$categoryId");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // 3 second connection timeout
    
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($curlError) {
        logMessage('ERROR', "CURL error connecting to category service: $curlError");
        return ['valid' => false, 'error' => "Error connecting to category service: $curlError"];
    }
    
    if ($statusCode !== 200) {
        logMessage('WARNING', "Category validation failed with status code: $statusCode");
        return ['valid' => false, 'error' => 'Category not found'];
    }
    
    logMessage('INFO', "Category ID $categoryId validated successfully");
    return ['valid' => true];
}

// Health check endpoint
if ($requestUri === '/health') {
    try {
        // Test database connection
        $pdo->query('SELECT 1');
        $databaseStatus = 'UP';
    } catch (Exception $e) {
        logMessage('ERROR', "Health check - Database connection failed: " . $e->getMessage());
        $databaseStatus = 'DOWN';
    }
    
    // Check category service if needed
    $categoryServiceStatus = 'UNKNOWN';
    try {
        $ch = curl_init("$categoryServiceUrl/health");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2); // Short timeout for health checks
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $categoryServiceStatus = ($statusCode >= 200 && $statusCode < 300) ? 'UP' : 'DOWN';
    } catch (Exception $e) {
        logMessage('WARNING', "Health check - Category service connection failed: " . $e->getMessage());
        $categoryServiceStatus = 'DOWN';
    }
    
    $status = ($databaseStatus === 'UP') ? 'UP' : 'DOWN';
    
    echo json_encode([
        'status' => $status,
        'database' => $databaseStatus,
        'dependencies' => [
            'category_service' => $categoryServiceStatus
        ]
    ]);
    exit;
}

// Process API requests
if (strpos($requestUri, $basePath) === 0) {
    $pathInfo = substr($requestUri, strlen($basePath));
    
    // Get all products
    if ($pathInfo === '' && $method === 'GET') {
        try {
            logMessage('INFO', "Getting all products");
            $stmt = $pdo->query('SELECT * FROM products');
            $products = $stmt->fetchAll();
            echo json_encode($products);
        } catch (PDOException $e) {
            logMessage('ERROR', "Failed to fetch products: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch products: ' . $e->getMessage()]);
        }
    }
    // Get a specific product
    elseif (preg_match('#^/(\d+)$#', $pathInfo, $matches) && $method === 'GET') {
        $productId = $matches[1];
        logMessage('INFO', "Getting product ID: $productId");
        
        try {
            $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if ($product) {
                echo json_encode($product);
            } else {
                logMessage('WARNING', "Product ID $productId not found");
                http_response_code(404);
                echo json_encode(['error' => 'Product not found']);
            }
        } catch (PDOException $e) {
            logMessage('ERROR', "Failed to fetch product $productId: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch product: ' . $e->getMessage()]);
        }
    }
    // Create a new product
    elseif ($pathInfo === '' && $method === 'POST') {
        $requestBody = file_get_contents('php://input');
        logMessage('INFO', "Creating new product. Request: $requestBody");
        $data = json_decode($requestBody, true);
        
        if (!$data || !isset($data['name']) || !isset($data['price']) || !isset($data['category_id'])) {
            logMessage('WARNING', "Invalid product creation request - missing required fields");
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }
        
        // Validate category exists by calling category service
        $categoryValidation = validateCategory($data['category_id']);
        if (!$categoryValidation['valid']) {
            http_response_code(400);
            echo json_encode(['error' => $categoryValidation['error']]);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare('
                INSERT INTO products (name, description, price, category_id)
                VALUES (?, ?, ?, ?)
            ');
            $stmt->execute([
                $data['name'],
                $data['description'] ?? '',
                $data['price'],
                $data['category_id']
            ]);
            
            $productId = $pdo->lastInsertId();
            logMessage('INFO', "Product created with ID: $productId");
            
            $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            http_response_code(201);
            echo json_encode($product);
        } catch (PDOException $e) {
            logMessage('ERROR', "Failed to create product: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create product: ' . $e->getMessage()]);
        }
    }
    // Update a product
    elseif (preg_match('#^/(\d+)$#', $pathInfo, $matches) && $method === 'PUT') {
        $productId = $matches[1];
        $requestBody = file_get_contents('php://input');
        logMessage('INFO', "Updating product ID: $productId. Request: $requestBody");
        $data = json_decode($requestBody, true);
        
        if (!$data) {
            logMessage('WARNING', "Invalid update request for product $productId - invalid JSON");
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            exit;
        }
        
        // If category_id is being updated, validate it exists
        if (isset($data['category_id'])) {
            $categoryValidation = validateCategory($data['category_id']);
            if (!$categoryValidation['valid']) {
                http_response_code(400);
                echo json_encode(['error' => $categoryValidation['error']]);
                exit;
            }
        }
        
        try {
            // First check if product exists
            $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                logMessage('WARNING', "Product ID $productId not found for update");
                http_response_code(404);
                echo json_encode(['error' => 'Product not found']);
                exit;
            }
            
            // Build update query
            $updates = [];
            $params = [];
            
            if (isset($data['name'])) {
                $updates[] = 'name = ?';
                $params[] = $data['name'];
            }
            if (isset($data['description'])) {
                $updates[] = 'description = ?';
                $params[] = $data['description'];
            }
            if (isset($data['price'])) {
                $updates[] = 'price = ?';
                $params[] = $data['price'];
            }
            if (isset($data['category_id'])) {
                $updates[] = 'category_id = ?';
                $params[] = $data['category_id'];
            }
            
            if (empty($updates)) {
                // No updates needed
                logMessage('INFO', "No changes required for product $productId");
                echo json_encode($product);
                exit;
            }
            
            // Add product ID to params
            $params[] = $productId;
            
            $stmt = $pdo->prepare('
                UPDATE products 
                SET ' . implode(', ', $updates) . '
                WHERE id = ?
            ');
            $stmt->execute($params);
            logMessage('INFO', "Product $productId updated successfully");
            
            // Fetch updated product
            $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            echo json_encode($product);
        } catch (PDOException $e) {
            logMessage('ERROR', "Failed to update product $productId: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update product: ' . $e->getMessage()]);
        }
    }
    // Delete a product
    elseif (preg_match('#^/(\d+)$#', $pathInfo, $matches) && $method === 'DELETE') {
        $productId = $matches[1];
        logMessage('INFO', "Deleting product ID: $productId");
        
        try {
            // First check if product exists
            $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                logMessage('WARNING', "Product ID $productId not found for deletion");
                http_response_code(404);
                echo json_encode(['error' => 'Product not found']);
                exit;
            }
            
            $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            logMessage('INFO', "Product $productId deleted successfully");
            
            echo json_encode(['message' => 'Product deleted successfully']);
        } catch (PDOException $e) {
            logMessage('ERROR', "Failed to delete product $productId: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete product: ' . $e->getMessage()]);
        }
    }
    // Get products by category
    elseif (preg_match('#^/category/(.+)$#', $pathInfo, $matches) && $method === 'GET') {
        $categoryId = $matches[1];
        logMessage('INFO', "Getting products for category ID: $categoryId");
        
        // Validate category exists
        $categoryValidation = validateCategory($categoryId);
        if (!$categoryValidation['valid']) {
            http_response_code(404);
            echo json_encode(['error' => $categoryValidation['error']]);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare('SELECT * FROM products WHERE category_id = ?');
            $stmt->execute([$categoryId]);
            $products = $stmt->fetchAll();
            
            echo json_encode($products);
        } catch (PDOException $e) {
            logMessage('ERROR', "Failed to fetch products for category $categoryId: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch products: ' . $e->getMessage()]);
        }
    }
    else {
        logMessage('WARNING', "Endpoint not found: $requestUri");
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }
} else {
    logMessage('WARNING', "API endpoint not found: $requestUri");
    http_response_code(404);
    echo json_encode(['error' => 'API endpoint not found']);
}