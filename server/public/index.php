<?php

/**
 * Collaborative Task Management System - API Entry Point
 *
 * This file serves as the main entry point for the PHP backend API.
 * It handles request routing, initializes the database connection,
 * and dispatches requests to the appropriate controllers.
 *
 * Tech Stack: PHP, Composer, Dotenv, PDO
 *
 * @package TaskManagementSystem
 * @author Your Name/Company
 * @version 1.0.0
 */

// -----------------------------------------------------------------------------
// 1. Autoloading and Environment Setup
// -----------------------------------------------------------------------------

// Require Composer's autoloader for automatic class loading.
// This ensures that classes from `vendor/` and `src/` are available.
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from the .env file.
// This is crucial for sensitive information like database credentials.
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    // Log or handle the error if .env file is not found, but don't stop execution in dev.
    // In production, this should be handled more robustly (e.g., fail fast).
    error_log("Warning: .env file not found or invalid path. Ensure it's in the project root. Error: " . $e->getMessage());
}


// -----------------------------------------------------------------------------
// 2. CORS (Cross-Origin Resource Sharing) Configuration
// -----------------------------------------------------------------------------

// Allow requests from specific origins (e.g., your React frontend).
// In a production environment, replace '*' with your actual frontend URL(s).
// Example: 'http://localhost:3000', 'https://your-frontend.com'
$allowedOrigins = [
    $_ENV['CLIENT_ORIGIN'] ?? 'http://localhost:3000', // Default for local development
    // Add other origins for interconnected services if needed
    // $_ENV['WHITEBOARD_SERVICE_ORIGIN'] ?? 'http://localhost:3001',
    // $_ENV['SOCIAL_MEDIA_SERVICE_ORIGIN'] ?? 'http://localhost:3002',
];

// Check if the request origin is allowed
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins) || (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development')) {
    header("Access-Control-Allow-Origin: " . $origin);
} else {
    // If origin is not allowed, you might want to return an error or default to a safe origin
    // For now, we'll allow it if not explicitly in the list but in dev mode, otherwise restrict.
    if (!isset($_ENV['APP_ENV']) || $_ENV['APP_ENV'] !== 'development') {
        // For production, uncomment the following line to strictly enforce allowed origins
        // header("HTTP/1.1 403 Forbidden");
        // exit("Forbidden: Origin not allowed.");
    }
}


// Allow specific HTTP methods.
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
// Allow specific headers to be sent by the client.
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// Allow credentials (e.g., cookies, HTTP authentication) to be sent.
header("Access-Control-Allow-Credentials: true");
// Set the maximum age (in seconds) for which the preflight request can be cached.
header("Access-Control-Max-Age: 86400");

// Handle preflight OPTIONS requests.
// Browsers send an OPTIONS request before the actual request to check CORS policies.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit();
}

// Set default content type for all responses to JSON.
header("Content-Type: application/json; charset=UTF-8");


// -----------------------------------------------------------------------------
// 3. Database Connection
// -----------------------------------------------------------------------------

// Include the database configuration file.
require_once __DIR__ . '/../config/database.php';

try {
    // Establish a PDO database connection.
    $pdo = connectDB();
} catch (PDOException $e) {
    // If database connection fails, return a 500 Internal Server Error.
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}


// -----------------------------------------------------------------------------
// 4. Routing and Request Dispatching
// -----------------------------------------------------------------------------

// Get the request URI and method.
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string from URI for routing purposes.
$path = parse_url($requestUri, PHP_URL_PATH);
// Remove any base path if the application is not in the root directory.
// For example, if your app is at example.com/api, $basePath would be '/api'.
$basePath = $_ENV['APP_BASE_PATH'] ?? ''; // e.g., '/api'
if ($basePath && strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}
$path = trim($path, '/'); // Remove leading/trailing slashes

// Split the path into segments.
$segments = explode('/', $path);

// Instantiate controllers.
// Pass the PDO connection to the controllers for database operations.
$taskController = new \App\Controllers\TaskController($pdo);

// Initialize response data and status code.
$responseData = [];
$statusCode = 200;

try {
    // Simple Router Logic
    switch ($segments[0]) {
        case 'tasks':
            // Handle /tasks and /tasks/{id} endpoints
            $taskId = $segments[1] ?? null;

            if ($taskId) {
                // Request for a specific task: /tasks/{id}
                switch ($requestMethod) {
                    case 'GET':
                        $responseData = $taskController->show($taskId);
                        break;
                    case 'PUT':
                        $input = json_decode(file_get_contents('php://input'), true);
                        if (!$input) {
                            throw new Exception("Invalid JSON input", 400);
                        }
                        $responseData = $taskController->update($taskId, $input);
                        break;
                    case 'DELETE':
                        $responseData = $taskController->destroy($taskId);
                        $statusCode = 204; // No Content for successful deletion
                        break;
                    default:
                        throw new Exception("Method Not Allowed", 405);
                }
            } else {
                // Request for all tasks: /tasks
                switch ($requestMethod) {
                    case 'GET':
                        $responseData = $taskController->index();
                        break;
                    case 'POST':
                        $input = json_decode(file_get_contents('php://input'), true);
                        if (!$input) {
                            throw new Exception("Invalid JSON input", 400);
                        }
                        $responseData = $taskController->store($input);
                        $statusCode = 201; // Created
                        break;
                    default:
                        throw new Exception("Method Not Allowed", 405);
                }
            }
            break;

        case 'projects':
            // Handle /projects/{projectId}/tasks endpoint
            $projectId = $segments[1] ?? null;
            if ($projectId && ($segments[2] ?? null) === 'tasks') {
                switch ($requestMethod) {
                    case 'GET':
                        $responseData = $taskController->getTasksByProject($projectId);
                        break;
                    default:
                        throw new Exception("Method Not Allowed", 405);
                }
            } else {
                // If it's just /projects or /projects/{id} without /tasks,
                // this API doesn't handle project CRUD directly yet.
                // This could be extended to a ProjectController.
                throw new Exception("Not Found", 404);
            }
            break;

        case 'health':
            // Simple health check endpoint for monitoring or service discovery
            // This could be used by a gateway or other microservices.
            $responseData = ['status' => 'ok', 'service' => 'task-management-api', 'timestamp' => time()];
            break;

        case '':
            // Root path, provide a simple API welcome message
            $responseData = ['message' => 'Welcome to the Collaborative Task Management API!', 'version' => '1.0'];
            break;

        default:
            // No route matched
            throw new Exception("Not Found", 404);
    }
} catch (Exception $e) {
    // Catch exceptions thrown during request processing.
    $statusCode = $e->getCode() ?: 500; // Use custom code if set, otherwise 500.
    if ($statusCode < 100 || $statusCode >= 600) { // Ensure valid HTTP status code range
        $statusCode = 500;
    }
    $responseData = ['message' => $e->getMessage()];

    // In development, include more details. In production, log and provide generic message.
    if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development') {
        $responseData['trace'] = $e->getTraceAsString();
        $responseData['file'] = $e->getFile();
        $responseData['line'] = $e->getLine();
    }
    error_log("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
}

// -----------------------------------------------------------------------------
// 5. Send Response
// -----------------------------------------------------------------------------

// Set the HTTP status code.
http_response_code($statusCode);

// Encode the response data to JSON and output it.
echo json_encode($responseData);

// Close the database connection (optional, PHP usually closes it automatically at script end).
$pdo = null;
?>