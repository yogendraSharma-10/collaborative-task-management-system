<?php

namespace App\Controllers;

use App\Models\Task;

/**
 * Class TaskController
 * Handles API requests related to tasks for the Collaborative Task Management System.
 * Provides RESTful CRUD operations for tasks, including filtering by project or assigned user.
 *
 * This controller assumes a basic routing mechanism in `server/public/index.php`
 * that maps incoming HTTP requests to the appropriate controller methods.
 */
class TaskController
{
    /**
     * Sets the necessary HTTP headers for JSON responses and CORS.
     * This method should be called at the beginning of any public controller method
     * that intends to return a JSON response.
     */
    private function setJsonHeaders(): void
    {
        header('