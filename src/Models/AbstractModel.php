<?php

namespace App\Models;

use App\Utils\Database;

/**
 * Abstract Model Base Class
 * 
 * Provides basic database connectivity and common interface for models
 */
abstract class AbstractModel
{
    /** @var \PDO Database connection instance */
    protected $db;

    /**
     * Initialize database connection
     */
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Retrieve all records
     * 
     * @return array List of records
     */
    abstract public function getAll(): array;

    /**
     * Retrieve a record by ID
     * 
     * @param string $id Record identifier
     * @return array|null Record data or null if not found
     */
    abstract public function getById($id): ?array;
}
