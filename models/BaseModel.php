<?php
/**
 * Base Model Class
 * Provides database operations and ORM-like functionality
 */

class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $guarded = [];
    protected $timestamps = true;
    protected $createdAtColumn = 'created_at';
    protected $updatedAtColumn = 'updated_at';
    protected $attributes = [];
    protected $original = [];
    protected $exists = false;

    /**
     * Constructor
     */
    public function __construct($attributes = []) {
        $this->db = Database::getInstance();
        $this->fill($attributes);
    }

    /**
     * Fill model with attributes
     */
    public function fill($attributes) {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Check if attribute is fillable
     */
    protected function isFillable($key) {
        if (!empty($this->guarded) && in_array($key, $this->guarded)) {
            return false;
        }

        if (!empty($this->fillable)) {
            return in_array($key, $this->fillable);
        }

        return true;
    }

    /**
     * Set attribute
     */
    public function setAttribute($key, $value) {
        if ($this->isFillable($key)) {
            $this->attributes[$key] = $value;
        }
        return $this;
    }

    /**
     * Get attribute
     */
    public function getAttribute($key, $default = null) {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Magic getter
     */
    public function __get($key) {
        return $this->getAttribute($key);
    }

    /**
     * Magic setter
     */
    public function __set($key, $value) {
        $this->setAttribute($key, $value);
    }

    /**
     * Check if attribute exists
     */
    public function __isset($key) {
        return isset($this->attributes[$key]);
    }

    /**
     * Unset attribute
     */
    public function __unset($key) {
        unset($this->attributes[$key]);
    }

    /**
     * Get all attributes
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * Get original attributes
     */
    public function getOriginal($key = null) {
        if ($key === null) {
            return $this->original;
        }
        return $this->original[$key] ?? null;
    }

    /**
     * Check if model exists in database
     */
    public function exists() {
        return $this->exists;
    }

    /**
     * Get primary key value
     */
    public function getKey() {
        return $this->getAttribute($this->primaryKey);
    }

    /**
     * Set primary key value
     */
    public function setKey($value) {
        return $this->setAttribute($this->primaryKey, $value);
    }

    /**
     * Save model to database
     */
    public function save() {
        if ($this->exists) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    /**
     * Insert new record
     */
    protected function insert() {
        $attributes = $this->attributes;

        // Add timestamps
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            if ($this->createdAtColumn) {
                $attributes[$this->createdAtColumn] = $now;
            }
            if ($this->updatedAtColumn) {
                $attributes[$this->updatedAtColumn] = $now;
            }
        }

        $id = $this->db->insert($this->table, $attributes);

        if ($id) {
            $this->setKey($id);
            $this->exists = true;
            $this->original = $attributes;
            $this->attributes[$this->primaryKey] = $id;
            return true;
        }

        return false;
    }

    /**
     * Update existing record
     */
    protected function update() {
        $attributes = $this->attributes;

        // Add updated timestamp
        if ($this->timestamps && $this->updatedAtColumn) {
            $attributes[$this->updatedAtColumn] = date('Y-m-d H:i:s');
        }

        $key = $this->getKey();
        $result = $this->db->update(
            $this->table,
            $attributes,
            "{$this->primaryKey} = ?",
            [$key]
        );

        if ($result) {
            $this->original = $attributes;
            return true;
        }

        return false;
    }

    /**
     * Delete record
     */
    public function delete() {
        if (!$this->exists) {
            return false;
        }

        $key = $this->getKey();
        $result = $this->db->delete(
            $this->table,
            "{$this->primaryKey} = ?",
            [$key]
        );

        if ($result) {
            $this->exists = false;
            return true;
        }

        return false;
    }

    /**
     * Find record by primary key
     */
    public static function find($id) {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = ?",
            [$id]
        );

        if ($result) {
            $instance->attributes = $result;
            $instance->original = $result;
            $instance->exists = true;
            return $instance;
        }

        return null;
    }

    /**
     * Find records by conditions
     */
    public static function where($column, $operator, $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $instance = new static();
        $query = new QueryBuilder($instance->db, $instance->table);
        return $query->where($column, $operator, $value);
    }

    /**
     * Get all records
     */
    public static function all() {
        $instance = new static();
        $results = $instance->db->fetchAll("SELECT * FROM {$instance->table}");

        $models = [];
        foreach ($results as $result) {
            $model = new static($result);
            $model->original = $result;
            $model->exists = true;
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Create new record
     */
    public static function create($attributes) {
        $instance = new static($attributes);
        $instance->save();
        return $instance;
    }

    /**
     * Get first record
     */
    public static function first() {
        $instance = new static();
        $result = $instance->db->fetch("SELECT * FROM {$instance->table} LIMIT 1");

        if ($result) {
            $instance->attributes = $result;
            $instance->original = $result;
            $instance->exists = true;
            return $instance;
        }

        return null;
    }

    /**
     * Count records
     */
    public static function count() {
        $instance = new static();
        $result = $instance->db->fetch("SELECT COUNT(*) as count FROM {$instance->table}");
        return $result['count'] ?? 0;
    }

    /**
     * Check if model is dirty (has unsaved changes)
     */
    public function isDirty($attribute = null) {
        if ($attribute === null) {
            return $this->attributes !== $this->original;
        }

        return ($this->attributes[$attribute] ?? null) !== ($this->original[$attribute] ?? null);
    }

    /**
     * Get dirty attributes
     */
    public function getDirty() {
        $dirty = [];
        foreach ($this->attributes as $key => $value) {
            if (!isset($this->original[$key]) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }
        return $dirty;
    }

    /**
     * Refresh model from database
     */
    public function refresh() {
        if (!$this->exists) {
            return false;
        }

        $key = $this->getKey();
        $result = $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$key]
        );

        if ($result) {
            $this->attributes = $result;
            $this->original = $result;
            return true;
        }

        return false;
    }

    /**
     * Convert to array
     */
    public function toArray() {
        return $this->attributes;
    }

    /**
     * Convert to JSON
     */
    public function toJson() {
        return json_encode($this->toArray());
    }

    /**
     * Handle dynamic method calls for relationships
     */
    public function __call($method, $parameters) {
        // Handle relationship methods
        if (method_exists($this, $method)) {
            return $this->$method(...$parameters);
        }

        throw new Exception("Method {$method} does not exist");
    }

    /**
     * Handle static method calls
     */
    public static function __callStatic($method, $parameters) {
        $instance = new static();
        return $instance->$method(...$parameters);
    }
}

/**
 * Simple Query Builder Class
 */
class QueryBuilder {
    private $db;
    private $table;
    private $wheres = [];
    private $orders = [];
    private $limit;
    private $offset;
    private $select = '*';

    public function __construct($db, $table) {
        $this->db = $db;
        $this->table = $table;
    }

    public function where($column, $operator, $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];

        return $this;
    }

    public function orderBy($column, $direction = 'ASC') {
        $this->orders[] = ['column' => $column, 'direction' => $direction];
        return $this;
    }

    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }

    public function select($columns) {
        $this->select = is_array($columns) ? implode(', ', $columns) : $columns;
        return $this;
    }

    public function get() {
        $sql = $this->buildSelectQuery();
        $results = $this->db->fetchAll($sql, $this->getBindings());

        $models = [];
        $class = get_class(new BaseModel()); // This should be the calling class
        foreach ($results as $result) {
            $model = new $class($result);
            $model->original = $result;
            $model->exists = true;
            $models[] = $model;
        }

        return $models;
    }

    public function first() {
        $this->limit(1);
        $results = $this->get();
        return !empty($results) ? $results[0] : null;
    }

    public function count() {
        $originalSelect = $this->select;
        $this->select = 'COUNT(*) as count';
        $result = $this->db->fetch($this->buildSelectQuery(), $this->getBindings());
        $this->select = $originalSelect;
        return $result['count'] ?? 0;
    }

    private function buildSelectQuery() {
        $sql = "SELECT {$this->select} FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWhereClause();
        }

        if (!empty($this->orders)) {
            $sql .= " ORDER BY " . $this->buildOrderClause();
        }

        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    private function buildWhereClause() {
        $clauses = [];
        foreach ($this->wheres as $where) {
            $clauses[] = "{$where['column']} {$where['operator']} ?";
        }
        return implode(' AND ', $clauses);
    }

    private function buildOrderClause() {
        $clauses = [];
        foreach ($this->orders as $order) {
            $clauses[] = "{$order['column']} {$order['direction']}";
        }
        return implode(', ', $clauses);
    }

    private function getBindings() {
        $bindings = [];
        foreach ($this->wheres as $where) {
            $bindings[] = $where['value'];
        }
        return $bindings;
    }
}