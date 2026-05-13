<?php

namespace App\Models;

use App\Config\Database;

/**
 * Class Task
 *
 * Represents a task within the Collaborative Task Management System.
 * Handles database interactions for tasks, including CRUD operations
 * and data validation.
 */
class Task
{
    /**
     * @var \PDO The database connection object.
     */
    private $conn;

    /**
     * @var string The name of the database table for tasks.
     */
    private $table_name = "tasks";

    // Task properties (public for easy access, but typically accessed via getters/setters in larger ORMs)
    public ?int $id;
    public int $project_id;
    public string