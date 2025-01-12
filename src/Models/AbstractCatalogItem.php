<?php

namespace App\Models;

use App\Utils\Database;

/**
 * Abstract Catalog Item Base Class
 * 
 * Provides base functionality for catalog items
 */
abstract class AbstractCatalogItem
{
    /** @var \PDO Database connection instance */
    protected $db;
    
    /** @var string|null Item identifier */
    protected $id;
    
    /** @var array Item data */
    protected $data;

    /**
     * Initialize database connection
     */
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Get item by ID
     * 
     * @param string $id Item identifier
     * @return mixed
     */
    abstract public function getById($id);

    /**
     * Get all items
     * 
     * @return array
     */
    abstract public function getAll();

    /**
     * Create new item
     * 
     * @param array $data Item data
     * @return mixed
     */
    abstract public function create(array $data);

    /**
     * Update existing item
     * 
     * @param string $id Item identifier
     * @param array $data Updated item data
     * @return mixed
     */
    abstract public function update($id, array $data);

    /**
     * Delete item
     * 
     * @param string $id Item identifier
     * @return mixed
     */
    abstract public function delete($id);
}
