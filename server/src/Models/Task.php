<?php

namespace App\Models;

use PDO;
use PDOException;

/**
 * Class Task
 *
 * Represents a task in the Collaborative Task Management System.
 * Handles database interactions for tasks, including creation, retrieval,
 * updating, and deletion.
 *
 * This model assumes the existence of a `tasks` table in the database
 * with columns corresponding to the public properties defined below.
 *
 * Table Schema (example):
 * CREATE TABLE tasks (
 *     id INT AUTO_INCREMENT PRIMARY KEY,
 *     project_id INT NOT NULL,
 *     title VARCHAR(255) NOT NULL,
 *     description TEXT,
 *     assigned_to INT, -- References a user ID, potentially from a central User service