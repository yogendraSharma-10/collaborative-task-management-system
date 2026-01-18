<?php

/**
 * Collaborative Task Management System - Backend Entry Point
 *
 * This file serves as the main entry point for the PHP backend API.
 * It handles request routing, initializes the database connection,
 * sets up CORS, and dispatches requests to the appropriate controllers.
 *
 * Tech Stack: PHP, Composer, Dotenv, PDO
 *
 * @package TaskManagementSystem
 * @author Your Name/Company
 * @version 1.0.0
 */

// -----------------------------------------------------------------------------
// 1. Autoload Composer Dependencies
// -----------------------------------------------------------------------------
// This line includes Composer's autoloader, which automatically loads
// all necessary classes from the 'vendor' directory and our 'src' directory
// based on the PSR-4 configuration in composer.json.
require_once __DIR__ . '/../vendor/autoload.php';

// -----------------------------------------------------------------------------
// 2. Load Environment Variables
// -----------------------------------------------------------------------------
// We use 'vlucas/phpdotenv' to load environment variables from the .env file.
// This keeps sensitive configuration (like database credentials) out of
// version control and makes it easy to manage different environments.
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// -----------------------------------------------------------------------------
// 3. Error Reporting (Development vs. Production)
// -----------------------------------------------------------------------------
// In a production environment, error display should be turned off for security.
// For development, it's useful to see errors directly.
if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
    // Log errors to a file in production
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
}

// -----------------------------------------------------------------------------
// 4. CORS (Cross-Origin Resource Sharing) Configuration
// -----------------------------------------------------------------------------
// These headers are crucial for allowing the React frontend (running on a
// different port or domain) to make requests to this PHP backend.
// The 'CLIENT_URL' environment variable should specify the frontend's origin.
$allowedOrigin = $_ENV['CLIENT_URL'] ?? '*'; // Fallback to '*' for development if not set
header("Access-Control-Allow-Origin: " . $allowedOrigin);
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS requests (sent by browsers before actual requests)
// If it's an OPTIONS request, we just send the CORS headers and exit.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// -----------------------------------------------------------------------------
// 5. Initialize Database Connection
// -----------------------------------------------------------------------------
// We use the Database class to establish a PDO connection.
// This connection will be passed to controllers and models.
require_once __DIR__ . '/../config/database.php';
use App\Config\Database;
use PDO;
use PDOException;

$pdo = null;
try {
    $db = new Database();
    $pdo = $db->connect();
} catch (PDOException $e) {
    // Log the error in production, display generic message
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Service Unavailable: Could not connect to the database.']);
    exit();
}

// -----------------------------------------------------------------------------
// 6. Request Routing
// -----------------------------------------------------------------------------
// This section implements a simple routing mechanism to direct incoming
// HTTP requests to the appropriate controller methods based on the URL path
// and HTTP method.
use App\Controllers\TaskController;

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Define the base API path for the Task Management service.
// This helps in maintaining a consistent API structure, especially in a
// microservice environment where different services might have their own
// versioned API prefixes (e.g., /api/v1/users, /api/v1/projects).
$baseApiPath = '/api/v1/tasks';

// Check if the request URI starts with our base API path
if (strpos($requestUri, $baseApiPath) === 0) {
    // Extract the path relative to the base API path
    $path = substr($requestUri, strlen($baseApiPath));
    $pathSegments = array_filter(explode('/', $path)); // Remove empty segments from path

    // Initialize the TaskController with the database connection
    $taskController = new TaskController($pdo);

    // Route handling for /api/v1/tasks
    if (empty($pathSegments)) {
        switch ($requestMethod) {
            case 'GET':
                $taskController->index(); // Get all tasks
                break;
            case 'POST':
                $taskController->store(); // Create a new task
                break;
            default:
                http_response_code(405); // Method Not Allowed
                echo json_encode(['message' => 'Method Not Allowed for /api/v1/tasks']);
                break;
        }
    }
    // Route handling for /api/v1/tasks/{id}
    elseif (count($pathSegments) === 1 && is_numeric($pathSegments[0])) {
        $id = (int) $pathSegments[0]; // Extract task ID
        switch ($requestMethod) {
            case 'GET':
                $taskController->show($id); // Get a single task by ID
                break;
            case 'PUT':
                $taskController->update($id); // Update a task by ID
                break;
            case 'DELETE':
                $taskController->destroy($id); // Delete a task by ID
                break;
            default:
                http_response_code(405); // Method Not Allowed
                echo json_encode(['message' => 'Method Not Allowed for /api/v1/tasks/{id}']);
                break;
        }
    }
    // If the path segments don't match expected patterns
    else {
        http_response_code(404); // Not Found
        echo json_encode(['message' => 'Resource Not Found']);
    }
} else {
    // If the request URI does not match any defined API path
    http_response_code(404); // Not Found
    echo json_encode(['message' => 'API Endpoint Not Found']);
}

// -----------------------------------------------------------------------------
// Cross-Project Context & Future Considerations
// -----------------------------------------------------------------------------
// This Task Management service is part of a larger interconnected system.
// In a full microservices architecture, this service would typically:
//
// - Be registered with an API Gateway (e.g., Nginx, Kong, AWS API Gateway)
//   which would handle routing, authentication, and rate limiting across
//   all services (Task Management, Whiteboard, Social Media, E-commerce).
//   The `baseApiPath` `/api/v1/tasks` is designed to fit this pattern.
//
// - Rely on a dedicated User Authentication/Authorization service.
//   User sessions and permissions would be managed externally, and this
//   service would validate tokens (e.g., JWTs) passed in the 'Authorization' header.
//
// - Integrate with other services:
//   - Real-time Collaborative Whiteboard: A task might have a linked whiteboard session.
//     This could involve storing a `whiteboard_session_id` in the task model
//     and making API calls to the Whiteboard service.
//   - Micro Social Media Dashboard: Task updates could be posted as activity
//     feed items on the social media dashboard.
//   - Multi-vendor E-commerce Marketplace: While less direct, a task could
//     be related to managing vendor orders or product listings in the marketplace.
//
// - Utilize a central Logging and Monitoring system.
//
// - Implement a robust Caching layer (e.g., Redis) for frequently accessed data.
//
// This `index.php