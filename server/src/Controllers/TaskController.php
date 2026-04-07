<?php

namespace App\Controllers;

use App\Models\Task;

/**
 * Class TaskController
 *
 * Handles API requests related to tasks within the Collaborative Task Management System.
 * Provides CRUD (Create, Read, Update, Delete) operations for tasks.
 * This controller interacts with the Task model to perform database operations
 * and returns JSON responses.
 */
class TaskController
{
    /**
     * @var Task The Task model instance used for database interactions.
     */
    private Task $taskModel;

    /**
     * TaskController constructor.
     *
     * Initializes the Task model. In a more complex framework, this would typically
     * be handled by a Dependency Injection Container.
     */
    public function __construct()
    {
        $this->taskModel = new Task();
    }

    /**
     * Retrieves a list of tasks.
     *
     * Supports optional filtering by `project_id` and `user_id` via query parameters.
     *
     * @return void JSON response containing an array of tasks or an error message.
     */
    public function index(): void
    {
        // In a real application, query parameters would be parsed from a Request object.
        // For simplicity, we're directly accessing $_GET.
        $projectId = $_GET['project_id'] ?? null;
        $userId = $_GET['user_id'] ?? null; // Represents the assigned user or current authenticated user

        try {
            $tasks = $this->taskModel->getAll($projectId, $userId);

            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(['status' => 'success', 'data' => $tasks]);
        } catch