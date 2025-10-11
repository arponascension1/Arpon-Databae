<?php

namespace Arpon\Database\Capsule;

use Arpon\Database\ConnectionInterface;
use Arpon\Database\DatabaseManager;
use Arpon\Database\Connectors\ConnectionFactory;
use Arpon\Database\Eloquent\Model;
use Arpon\Database\Schema\Builder;

class Manager
{
    /**
     * The database manager instance.
     *
     * @var DatabaseManager
     */
    protected DatabaseManager $manager;

    /**
     * The connection factory instance.
     *
     * @var ConnectionFactory
     */
    protected ConnectionFactory $factory;

    /**
     * The database connections configuration.
     *
     * @var array
     */
    protected array $connections = [];

    /**
     * The default connection name.
     *
     * @var string
     */
    protected string $default = 'default';

    /**
     * The global capsule instance.
     *
     * @var static
     */
    protected static $instance;

    /**
     * Create a new database capsule manager.
     *
     * @return void
     */
    public function __construct()
    {
        $this->factory = new ConnectionFactory();
        $this->setupManager();
    }

    /**
     * Setup the default database configuration.
     *
     * @return void
     */
    protected function setupManager(): void
    {
        $config = [
            'default' => $this->default,
            'connections' => $this->connections,
        ];

        $this->manager = new DatabaseManager($config, $this->factory);
    }

    /**
     * Add a connection to the manager.
     *
     * @param  array  $config
     * @param string $name
     * @return void
     */
    public function addConnection(array $config, string $name = 'default'): void
    {
        // Add required default values if not present
        $config = array_merge([
            'prefix' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ], $config);

        $this->connections[$name] = $config;

        // If this is the first connection, make it default
        if (empty($this->default) || $name === 'default') {
            $this->default = $name;
        }

        $this->setupManager();
    }

    /**
     * Get the database manager instance.
     *
     * @return DatabaseManager
     */
    public function getDatabaseManager(): DatabaseManager
    {
        return $this->manager;
    }

    /**
     * Get a connection instance from the manager.
     *
     * @param string|null $connection
     * @return ConnectionInterface
     */
    public function connection(string $connection = null): ConnectionInterface
    {
        return $this->manager->connection($connection);
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @param string|null $connection
     * @return Builder
     */
    public function schema(string $connection = null): Builder
    {
        return $this->connection($connection)->getSchemaBuilder();
    }

    /**
     * Get a query builder for the given table.
     *
     * @param string $table
     * @param string|null $connection
     * @return \Arpon\Database\Query\Builder
     */
    private function tableInstance(string $table, string $connection = null): \Arpon\Database\Query\Builder
    {
        return $this->connection($connection)->table($table);
    }

    /**
     * Get a query builder for the given table (static method only).
     *
     * @param string $table
     * @param string|null $connection
     * @return \Arpon\Database\Query\Builder
     */
    public static function table(string $table, string $connection = null): \Arpon\Database\Query\Builder
    {
        if (!static::$instance) {
            throw new \RuntimeException('Capsule not set as global. Call setAsGlobal() first.');
        }
        
        return static::$instance->tableInstance($table, $connection);
    }

    /**
     * Make this capsule instance available globally.
     *
     * @return void
     */
    public function setAsGlobal(): void
    {
        static::$instance = $this;
        Model::setConnectionResolver($this->manager);
    }

    /**
     * Bootstrap Eloquent so it is ready for usage.
     *
     * @return void
     */
    public function bootEloquent(): void
    {
        Model::setConnectionResolver($this->manager);

        // If we have connections, set the default
        if (!empty($this->connections)) {
            Model::setDefaultConnection($this->default);
        }
    }

    /**
     * Set the default connection name.
     *
     * @param string $name
     * @return void
     */
    public function setDefaultConnection(string $name): void
    {
        $this->default = $name;
        $this->setupManager();
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection(): string
    {
        return $this->default;
    }

    /**
     * Get all of the connections.
     *
     * @return array
     */
    public function getConnections(): array
    {
        return $this->connections;
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        // Handle table() method specifically to maintain backward compatibility
        if ($method === 'table') {
            $table = $parameters[0] ?? null;
            $connection = $parameters[1] ?? null;
            return $this->tableInstance($table, $connection);
        }
        
        return $this->connection()->$method(...$parameters);
    }

    /**
     * Get the global capsule instance.
     *
     * @return static
     */
    public static function getInstance()
    {
        return static::$instance;
    }



    /**
     * Run a select statement against the database (static version).
     *
     * @param string $query
     * @param array $bindings
     * @param string|null $connection
     * @return array
     */
    public static function select(string $query, array $bindings = [], string $connection = null): array
    {
        if (!static::$instance) {
            throw new \RuntimeException('Capsule not set as global. Call setAsGlobal() first.');
        }

        return static::$instance->connection($connection)->select($query, $bindings);
    }

    /**
     * Run an insert statement against the database (static version).
     *
     * @param string $query
     * @param array $bindings
     * @param string|null $connection
     * @return bool
     */
    public static function insert(string $query, array $bindings = [], string $connection = null): bool
    {
        if (!static::$instance) {
            throw new \RuntimeException('Capsule not set as global. Call setAsGlobal() first.');
        }

        return static::$instance->connection($connection)->insert($query, $bindings);
    }

    /**
     * Run an update statement against the database (static version).
     *
     * @param string $query
     * @param array $bindings
     * @param string|null $connection
     * @return int
     */
    public static function update(string $query, array $bindings = [], string $connection = null): int
    {
        if (!static::$instance) {
            throw new \RuntimeException('Capsule not set as global. Call setAsGlobal() first.');
        }

        return static::$instance->connection($connection)->update($query, $bindings);
    }

    /**
     * Run a delete statement against the database (static version).
     *
     * @param string $query
     * @param array $bindings
     * @param string|null $connection
     * @return int
     */
    public static function delete(string $query, array $bindings = [], string $connection = null): int
    {
        if (!static::$instance) {
            throw new \RuntimeException('Capsule not set as global. Call setAsGlobal() first.');
        }

        return static::$instance->connection($connection)->delete($query, $bindings);
    }

    /**
     * Execute a statement and return the boolean result (static version).
     *
     * @param string $query
     * @param array $bindings
     * @param string|null $connection
     * @return bool
     */
    public static function statement(string $query, array $bindings = [], string $connection = null): bool
    {
        if (!static::$instance) {
            throw new \RuntimeException('Capsule not set as global. Call setAsGlobal() first.');
        }

        return static::$instance->connection($connection)->statement($query, $bindings);
    }

    /**
     * Dynamically pass static method calls to the global instance.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public static function __callStatic(string $method, array $parameters)
    {
        if (!static::$instance) {
            throw new \RuntimeException('Capsule not set as global. Call setAsGlobal() first.');
        }

        // First check if the method exists on the instance
        if (method_exists(static::$instance, $method)) {
            return static::$instance->$method(...$parameters);
        }

        // If not, try to call it via the __call method (which forwards to connection)
        return static::$instance->__call($method, $parameters);
    }
}