<?php

namespace App\Controllers;

use App\Models\Task;
use Exception;

/**
 * TaskController
 *
 * Handles API requests related to tasks. This includes CRUD operations
 * (Create, Read, Update, Delete) for tasks within the Collaborative Task Management System.
 *
 * Assumes a basic routing mechanism in `server/public/index.php` that maps
 * incoming HTTP requests to the appropriate methods in this controller.
 *
 * For a production system, consider adding:
 * - Robust input validation (e.g., using a dedicated validation library).
 * - Authentication and Authorization middleware to protect endpoints.
 * - Dependency Injection for the Task model.
 * - Centralized error logging.
 */
class TaskController
{
    /**
     * @var Task The Task model instance.
     */
    private $taskModel;

    /**
     * Constructor
     * Initializes the Task model. In a more advanced framework, this would typically
     * be injected via a Dependency Injection container.
     */
    public function __construct()
    {
        $this->taskModel = new Task();
    }

    /**
     * Retrieves a list of tasks.
     * Supports filtering by project_id, assigned_to_user_id, or created_by_user_id via query parameters.
     *
     * HTTP Method: GET
     * Endpoint: /api/tasks
     * Query Params:
     *   - project_id (optional): Filter tasks by project.
     *   - assigned_to_user_id (optional): Filter tasks assigned to a specific user.
     *   - created_by_user_id (optional): Filter tasks created by a specific user.
     *
     * @return void JSON response containing tasks or an error message.
     */
    public function index(): void
    {
        header('Content-Type: application/json');

        try {
            $filters = [];
            if (isset($_GET['project_id']) && is_numeric($_GET['project_id'])) {
                $filters['project_id'] = (int)$_GET['project_id'];
            }
            if (isset($_GET['assigned_to_user_id']) && is_numeric($_GET['assigned_to_user_id'])) {
                $filters['assigned_to_user_id'] = (int)$_GET['assigned_to_user_id'];
            }
            if (isset($_GET['created_by_user_id']) && is_numeric($_GET['created_by_user_id'])) {
                $filters['created_by_user_id'] = (int)$_GET['created_by_user_id'];
            }

            $tasks = $this->taskModel->getAll($filters);
            http_response_code(200);
            echo json_encode(['status' => 'success', 'data' => $tasks]);
        } catch (Exception $e) {
            // Log the error for debugging in a production environment
            error_log("Error retrieving tasks: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to retrieve tasks.', 'details' => $e->getMessage()]);
        }
    }

    /**
     * Retrieves a single task by its ID.
     *
     * HTTP Method: GET
     * Endpoint: /api/tasks/{id}
     *
     * @param int $id The ID of the task to retrieve.
     * @return void JSON response containing the task or an error message.
     */
    public function show(int $id): void
    {
        header('Content-Type: application/json');

        try {
            $task = $this->taskModel->getById($id);

            if ($task) {
                http_response_code(200);
                echo json_encode(['status' => 'success', 'data' => $task]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Task not found.']);
            }
        } catch (Exception $e) {
            error_log("Error retrieving task ID {$id}: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to retrieve task.', 'details' => $e->getMessage()]);
        }
    }

    /**
     * Creates a new task.
     *
     * HTTP Method: POST
     * Endpoint: /api/tasks
     * Request Body (JSON):
     *   - title (string, required): The title of the task.
     *   - project_id (int, required): The ID of the project the task belongs