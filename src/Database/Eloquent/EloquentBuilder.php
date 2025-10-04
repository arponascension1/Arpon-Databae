<?php

namespace Arpon\Database\Eloquent;

use Arpon\Database\Query\Builder as QueryBuilder;
use Closure;
use BadMethodCallException;
use Error;

class EloquentBuilder
{
    /**
     * The base query builder instance.
     */
    protected QueryBuilder $query;

    /**
     * The model being queried.
     */
    protected Model $model;

    /**
     * Create a new Eloquent query builder instance.
     */
    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }

    /**
     * Set the model instance for the model being queried.
     */
    public function setModel(Model $model): static
    {
        $this->model = $model;

        $this->query->from($model->getTable());

        return $this;
    }

    /**
     * Get the model instance being queried.
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * The relationships that should be eager loaded.
     */
    protected array $eagerLoad = [];

    /**
     * Set the relationships that should be eager loaded.
     *
     * @param  mixed  ...$relations
     * @return $this
     */
    public function with(...$relations): static
    {
        $eagerLoad = $this->parseWithRelations(is_array($relations[0]) ? $relations[0] : $relations);

        $this->eagerLoad = array_merge($this->eagerLoad, $eagerLoad);

        return $this;
    }

    /**
     * Parse a list of relations into individuals.
     *
     * @param  array  $relations
     * @return array
     */
    protected function parseWithRelations(array $relations): array
    {
        $results = [];

        foreach ($relations as $name => $constraints) {
            // If the "relation" value is actually a numeric key, we can assume that no
            // constraints have been specified for the eager load and we'll just put
            // an empty Closure with the loader so that we can treat all the same.
            if (is_numeric($name)) {
                $name = $constraints;
                [$name, $constraints] = [$name, function () {
                    //
                }];
            }

            // We need to separate out any nested includes, which allows the developers
            // to load deep relationships using "dots" without stating each level of
            // the relationship with its own key in the array of eager load names.
            $results = $this->addNestedWiths($name, $results);

            $results[$name] = $constraints;
        }

        return $results;
    }

    /**
     * Parse the nested relationships in a relation.
     *
     * @param  string  $name
     * @param  array  $results
     * @return array
     */
    protected function addNestedWiths(string $name, array $results): array
    {
        $progress = [];

        // If the relation has already been set on the result array, we will not set it
        // again, since that would override any constraints that were already placed
        // on the relationships. We will only set the ones that are not specified.
        foreach (explode('.', $name) as $segment) {
            $progress[] = $segment;

            if (! isset($results[$last = implode('.', $progress)])) {
                $results[$last] = function () {
                    //
                };
            }
        }

        return $results;
    }

    /**
     * Get the relationships being eagerly loaded.
     *
     * @return array
     */
    public function getEagerLoads(): array
    {
        return $this->eagerLoad;
    }

    /**
     * Set the relationships being eagerly loaded.
     *
     * @param  array  $eagerLoad
     * @return $this
     */
    public function setEagerLoads(array $eagerLoad): static
    {
        $this->eagerLoad = $eagerLoad;

        return $this;
    }

    /**
     * Eager load the relationships for the models.
     *
     * @param  array  $models
     * @return array
     */
    public function eagerLoadRelations(array $models): array
    {
        foreach ($this->eagerLoad as $name => $constraints) {
            // For nested eager loads we'll skip loading them here and they will be set as an
            // eager load on the query to retrieve the relation so that they will be eager
            // loaded on that query, because that is where they get hydrated as models.
            if (! str_contains($name, '.')) {
                $models = $this->eagerLoadRelation($models, $name, $constraints);
            }
        }

        return $models;
    }

    /**
     * Eagerly load the relationship on a set of models.
     *
     * @param  array  $models
     * @param  string  $name
     * @param  \Closure  $constraints
     * @return array
     */
    protected function eagerLoadRelation(array $models, string $name, \Closure $constraints): array
    {
        // First we will "back up" the existing where conditions on the query so we can
        // add our eager constraints. Then we will merge the wheres that were on the
        // query back to it in order that any where conditions might be specified.
        $relation = $this->getRelation($name);

        $relation->addEagerConstraints($models);

        $constraints($relation);

        // Once we have the results, we just match those back up to their parent models
        // using the relationship instance. Then we just return the finished arrays
        // of models which have been eagerly hydrated and are readied for return.
        return $relation->match(
            $relation->initRelation($models, $name),
            $relation->getEager(),
            $name
        );
    }

    /**
     * Get the relation instance for the given relation name.
     *
     * @param  string  $name
     * @return \Arpon\Database\Eloquent\Relations\Relation
     */
    public function getRelation(string $name)
    {
        return \Arpon\Database\Eloquent\Relations\Relation::noConstraints(function () use ($name) {
            try {
                return $this->getModel()->$name();
            } catch (\BadMethodCallException $e) {
                throw new \Exception("Call to undefined relationship [{$name}] on model [".get_class($this->getModel())."].");
            }
        });
    }

    /**
     * Find a model by its primary key.
     */
    public function find($id, array $columns = ['*']): ?Model
    {
        if (is_array($id) || $id instanceof Collection) {
            return $this->findMany($id, $columns);
        }

        return $this->whereKey($id)->first($columns);
    }

    /**
     * Find multiple models by their primary keys.
     */
    public function findMany($ids, array $columns = ['*']): Collection
    {
        $ids = $ids instanceof Collection ? $ids->modelKeys() : $ids;

        if (empty($ids)) {
            return $this->model->newCollection();
        }

        return $this->whereKey($ids)->get($columns);
    }

    /**
     * Find a model by its primary key or throw an exception.
     */
    public function findOrFail($id, array $columns = ['*']): Model
    {
        $result = $this->find($id, $columns);

        $id = $id instanceof Collection ? $id->modelKeys() : $id;

        if (is_array($id)) {
            if (count($result) !== count(array_unique($id))) {
                throw new ModelNotFoundException();
            }

            return $result;
        }

        if (is_null($result)) {
            throw new ModelNotFoundException();
        }

        return $result;
    }

    /**
     * Execute the query and get the first result.
     */
    public function first(array $columns = ['*']): ?Model
    {
        return $this->take(1)->get($columns)->first();
    }

    /**
     * Execute the query and get the first result or throw an exception.
     */
    public function firstOrFail(array $columns = ['*']): Model
    {
        if (!is_null($model = $this->first($columns))) {
            return $model;
        }

        throw new ModelNotFoundException();
    }

    /**
     * Execute the query as a "select" statement.
     */
    public function get(array $columns = ['*']): Collection
    {
        $builder = $this->applyScopes();

        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded, which will solve the
        // n+1 query issue for the developers in most cases.
        $models = $builder->getModels($columns);

        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded. This will prevent the
        // n+1 query problem by loading all relationships in separate queries.
        if (count($models) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $this->model->newCollection($models);
    }

    /**
     * Paginate the given query.
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], string $pageName = 'page', ?int $page = null): array
    {
        $page = $page ?: 1;
        $total = $this->toBase()->getCountForPagination();

        $results = $total ? $this->forPage($page, $perPage)->get($columns) : $this->model->newCollection();

        return [
            'data' => $results,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
            'from' => (($page - 1) * $perPage) + 1,
            'to' => min($total, $page * $perPage),
        ];
    }

    /**
     * Constrain the query to the next "page" of results after a given ID.
     */
    public function forPage(int $page, int $perPage = 15): static
    {
        return $this->offset(($page - 1) * $perPage)->limit($perPage);
    }

    /**
     * Get the hydrated models without eager loading.
     */
    public function getModels(array $columns = ['*']): array
    {
        $results = $this->query->get($columns);
        
        if ($results instanceof \Arpon\Database\Support\Collection) {
            $results = $results->all();
        }
        
        return $this->model->hydrate($results)->all();
    }

    /**
     * Add a where clause on the primary key to the query.
     */
    public function whereKey($id): static
    {
        if (is_array($id) || $id instanceof Collection) {
            $this->query->whereIn($this->model->getKeyName(), $id);

            return $this;
        }

        return $this->where($this->model->getKeyName(), '=', $id);
    }

    /**
     * Add a where clause to the query.
     */
    public function where($column, $operator = null, $value = null, string $boolean = 'and'): static
    {
        if ($column instanceof Closure && is_null($operator)) {
            $column($query = $this->model->newQueryWithoutRelationships());

            $this->query->addNestedWhereQuery($query->getQuery(), $boolean);
        } else {
            $this->query->where($column, $operator, $value, $boolean);
        }

        return $this;
    }

    /**
     * Add an "or where" clause to the query.
     */
    public function orWhere($column, $operator = null, $value = null): static
    {
        [$value, $operator] = $this->query->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->where($column, $operator, $value, 'or');
    }

    /**
     * Add a "where in" clause to the query.
     */
    public function whereIn(string $column, $values, string $boolean = 'and', bool $not = false): static
    {
        $this->query->whereIn($column, $values, $boolean, $not);

        return $this;
    }

    /**
     * Add a "where not in" clause to the query.
     */
    public function whereNotIn(string $column, $values, string $boolean = 'and'): static
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Set the "limit" value of the query.
     */
    public function take(int $value): static
    {
        return $this->limit($value);
    }

    /**
     * Alias to set the "limit" value of the query.
     */
    public function limit(int $value): static
    {
        $this->query->limit($value);

        return $this;
    }

    /**
     * Set the "offset" value of the query.
     */
    public function offset(int $value): static
    {
        $this->query->offset($value);

        return $this;
    }

    /**
     * Set the "offset" value of the query.
     */
    public function skip(int $value): static
    {
        return $this->offset($value);
    }

    /**
     * Add an "order by" clause to the query.
     */
    public function orderBy($column, string $direction = 'asc'): static
    {
        $this->query->orderBy($column, $direction);

        return $this;
    }

    /**
     * Save a new model and return the instance.
     */
    public function create(array $attributes = []): Model
    {
        return tap($this->newModelInstance($attributes), function ($instance) {
            $instance->save();
        });
    }

    /**
     * Create a new instance of the model being queried.
     */
    public function newModelInstance(array $attributes = []): Model
    {
        return $this->model->newInstance($attributes);
    }

    /**
     * Update records in the database.
     */
    public function update(array $values): int
    {
        return $this->toBase()->update($this->addUpdatedAtColumn($values));
    }

    /**
     * Insert new records into the database.
     */
    public function insert(array $values): bool
    {
        // Since every insert gets treated like a batch insert, we will make sure the
        // bindings are structured in a way that is convenient when building the
        // SQL statements for the insert operation.
        if (empty($values)) {
            return true;
        }

        if (!is_array(reset($values))) {
            $values = [$values];
        }

        return $this->toBase()->insert($values);
    }

    /**
     * Insert new records into the database while ignoring errors.
     */
    public function insertGetId(array $values, ?string $sequence = null)
    {
        return $this->toBase()->insertGetId($values, $sequence);
    }

    /**
     * Delete records from the database.
     */
    public function delete(): mixed
    {
        if (isset($this->onDelete)) {
            return call_user_func($this->onDelete, $this);
        }

        return $this->toBase()->delete();
    }

    /**
     * Add the "updated at" column to an array of values.
     */
    protected function addUpdatedAtColumn(array $values): array
    {
        if (!$this->model->usesTimestamps() ||
            is_null($this->model->getUpdatedAtColumn())) {
            return $values;
        }

        $column = $this->model->getUpdatedAtColumn();

        $values = array_merge(
            [$column => $this->model->freshTimestampString()],
            $values
        );

        return $values;
    }

    /**
     * Get the underlying query builder instance.
     */
    public function getQuery(): QueryBuilder
    {
        return $this->query;
    }

    /**
     * Set the underlying query builder instance.
     */
    public function setQuery(QueryBuilder $query): static
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get a base query builder instance.
     */
    public function toBase(): QueryBuilder
    {
        return $this->applyScopes()->getQuery();
    }

    /**
     * Apply the scopes to the Eloquent builder instance and return it.
     */
    public function applyScopes(): static
    {
        if (!$this->scopes) {
            return $this;
        }

        $builder = clone $this;

        foreach ($this->scopes as $identifier => $scope) {
            if (!isset($builder->scopes[$identifier])) {
                continue;
            }

            $builder->callScope(function (EloquentBuilder $builder) use ($scope) {
                // If the scope is a Closure we will call the scope with the builder instance
                // and our application container instance, then the scope can do whatever it
                // needs to the query to implement the custom functionality.
                if ($scope instanceof Closure) {
                    $scope($builder);
                }
            });
        }

        return $builder;
    }

    /**
     * The global scopes applied to the builder.
     */
    protected array $scopes = [];

    /**
     * Apply the given scope on the current builder instance.
     */
    protected function callScope(callable $scope, array $parameters = []): mixed
    {
        array_unshift($parameters, $this);

        return $scope(...array_values($parameters)) ?? $this;
    }

    /**
     * Dynamically handle calls into the query instance.
     */
    public function __call(string $method, array $parameters)
    {
        if (method_exists($this->model, $scope = 'scope' . ucfirst($method))) {
            return $this->callScope([$this->model, $scope], $parameters);
        }

        $this->forwardCallTo($this->query, $method, $parameters);

        return $this;
    }

    /**
     * Forward a method call to the given object.
     */
    protected function forwardCallTo($object, string $method, array $parameters)
    {
        try {
            return $object->{$method}(...$parameters);
        } catch (Error|BadMethodCallException $e) {
            $pattern = '~^Call to undefined method (?P<class>[^:]+)::(?P<method>[^\(]+)\(\)$~';

            if (!preg_match($pattern, $e->getMessage(), $matches)) {
                throw $e;
            }

            if ($matches['class'] != get_class($object) ||
                $matches['method'] != $method) {
                throw $e;
            }

            throw new BadMethodCallException(sprintf(
                'Call to undefined method %s::%s()',
                static::class, $method
            ));
        }
    }

    /**
     * Dynamically handle calls into the query instance.
     */
    public function __clone()
    {
        $this->query = clone $this->query;
    }

    /**
     * Increment a column's value by a given amount.
     */
    public function increment(string $column, $amount = 1, array $extra = []): int
    {
        return $this->query->increment($column, $amount, $extra);
    }

    /**
     * Decrement a column's value by a given amount.
     */
    public function decrement(string $column, $amount = 1, array $extra = []): int
    {
        return $this->query->decrement($column, $amount, $extra);
    }

    /**
     * Order by the latest records (usually by created_at desc).
     */
    public function latest(string $column = 'created_at'): static
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Order by the oldest records (usually by created_at asc).
     */
    public function oldest(string $column = 'created_at'): static
    {
        return $this->orderBy($column, 'asc');
    }
}

// Utility function
if (!function_exists('tap')) {
    function tap($value, $callback = null)
    {
        if (is_null($callback)) {
            return new class($value) {
                public $target;

                public function __construct($target)
                {
                    $this->target = $target;
                }

                public function __call($method, $parameters)
                {
                    $this->target->{$method}(...$parameters);

                    return $this->target;
                }
            };
        }

        $callback($value);

        return $value;
    }
}