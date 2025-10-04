<?php

namespace Arpon\Database\Capsule;

use Arpon\Database\DatabaseManager;
use Arpon\Database\Connectors\ConnectionFactory;
use Arpon\Database\Eloquent\Model;

class Manager
{
    /**
     * The database manager instance.
     *
     * @var \Arpon\Database\DatabaseManager
     */
    protected $manager;

    /**
     * The connection factory instance.
     *
     * @var \Arpon\Database\Connectors\ConnectionFactory
     */
    protected $factory;

    /**
     * The database connections configuration.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * The default connection name.
     *
     * @var string
     */
    protected $default = 'default';

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
    protected function setupManager()
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
     * @param  string  $name
     * @return void
     */
    public function addConnection(array $config, $name = 'default')
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
     * @return \Arpon\Database\DatabaseManager
     */
    public function getDatabaseManager()
    {
        return $this->manager;
    }

    /**
     * Get a connection instance from the manager.
     *
     * @param  string|null  $connection
     * @return \Arpon\Database\ConnectionInterface
     */
    public function connection($connection = null)
    {
        return $this->manager->connection($connection);
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @param  string|null  $connection
     * @return \Arpon\Database\Schema\Builder
     */
    public function schema($connection = null)
    {
        return $this->connection($connection)->getSchemaBuilder();
    }

    /**
     * Get a query builder for the given table.
     *
     * @param  string  $table
     * @param  string|null  $connection
     * @return \Arpon\Database\Query\Builder
     */
    public function table($table, $connection = null)
    {
        return $this->connection($connection)->table($table);
    }

    /**
     * Make this capsule instance available globally.
     *
     * @return void
     */
    public function setAsGlobal()
    {
        Model::setConnectionResolver($this->manager);
    }

    /**
     * Bootstrap Eloquent so it is ready for usage.
     *
     * @return void
     */
    public function bootEloquent()
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
     * @param  string  $name
     * @return void
     */
    public function setDefaultConnection($name)
    {
        $this->default = $name;
        $this->setupManager();
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return $this->default;
    }

    /**
     * Get all of the connections.
     *
     * @return array
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->connection()->$method(...$parameters);
    }
}