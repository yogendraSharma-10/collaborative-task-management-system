<?php

namespace App\Models;

use PDO;
use DateTime;

/**
 * Class Task
 *
 * Represents a task within the Collaborative Task Management System.
 * Handles database interactions for tasks, including creation, retrieval,
 * updating, and deletion.
 */
class Task
{
    /**
     * @var int|null The unique identifier for the task. Null for new tasks.
     */
    public ?int $id;

    /**
     * @var int The ID of the project this task belongs to.
     */
    public int $project_id;

    /**
     * @var string The title of the task.
     */
    public string $title;

    /**
     * @var