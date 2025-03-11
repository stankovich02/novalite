<?php

namespace NovaLite\Database\Query;

use NovaLite\Application;
use NovaLite\Database\Database;
use NovaLite\Database\Query\Connections\MySQL;
use NovaLite\Database\Query\Connections\PostgreSQL;
use NovaLite\Exceptions\ModelNotFoundException;

class Builder implements QueryBuilderInterface
{
    private QueryBuilderInterface $connectionInstance;
    private array $connections = [
        'mysql' => MySQL::class,
        'pgsql' => PostgreSQL::class
    ];
    public function __construct(string $table)
    {
        $this->connectionInstance = new $this->connections[Database::getConnectionType()]($table);
    }
    public function setInstance($instance): void
    {
        $this->connectionInstance->setInstance($instance);
    }
    public function select(string ...$columns) : self
    {
        $this->connectionInstance->select(...$columns);

        return $this;
    }
    public function distinct() : self
    {
        $this->connectionInstance->distinct();

        return $this;
    }
    public function join(string $table, string|\Closure $first, string $operator, string $second) : self
    {
        $this->connectionInstance->join($table, $first, $operator, $second);

        return $this;
    }
    public function leftJoin(string $table, string|\Closure $first, string $operator, string $second) : self
    {
        $this->connectionInstance->leftJoin($table, $first, $operator, $second);

        return $this;
    }
    public function rightJoin(string $table, string|\Closure $first, string $operator, string $second) : self
    {
        $this->connectionInstance->rightJoin($table, $first, $operator, $second);

        return $this;
    }
    public function where(string $column, string $operator, string $value) : self
    {
        $this->connectionInstance->where($column, $operator, $value);

        return $this;
    }
    public function orWhere(string $column, string $operator, string $value) : self
    {
        $this->connectionInstance->orWhere($column, $operator, $value);

        return $this;
    }
    public function whereNot(string $column, string $operator, string $value) : self
    {
        $this->connectionInstance->whereNot($column, $operator, $value);

        return $this;
    }
    public function orWhereNot(string $column, string $operator, string $value) : self
    {
        $this->connectionInstance->orWhereNot($column, $operator, $value);

        return $this;
    }
    public function whereIn(string $column, array $values) : self
    {
        $this->connectionInstance->whereIn($column, $values);

        return $this;
    }
    public function orWhereIn(string $column, array $values) : self
    {
        $this->connectionInstance->orWhereIn($column, $values);

        return $this;
    }
    public function whereNotIn(string $column, array $values) : self
    {
        $this->connectionInstance->whereNotIn($column, $values);

        return $this;
    }
    public function orWhereNotIn(string $column, array $values) : self
    {
        $this->connectionInstance->orWhereNotIn($column, $values);

        return $this;
    }
    public function whereAny(array $columns, string $operator, string $value) : self
    {
        $this->connectionInstance->whereAny($columns, $operator, $value);

        return $this;
    }
    public function whereAll(array $columns, string $operator, string $value) : self
    {
        $this->connectionInstance->whereAll($columns, $operator, $value);

        return $this;
    }
    public function whereNone(array $columns, string $operator, string $value) : self
    {
        $this->connectionInstance->whereNone($columns, $operator, $value);

        return $this;
    }
    public function whereLike(string $column, string $value) : QueryBuilderInterface
    {
       $this->connectionInstance->whereLike($column, $value);

       return $this->connectionInstance;
    }
    public function orWhereLike(string $column, string $value) : self
    {
        $this->connectionInstance->orWhereLike($column, $value);

        return $this;
    }
    public function whereNotLike(string $column, string $value) : self
    {
        $this->connectionInstance->whereNotLike($column, $value);

        return $this;
    }
    public function orWhereNotLike(string $column, string $value) : self
    {
        $this->connectionInstance->orWhereNotLike($column, $value);

        return $this;
    }
    public function whereBetween(string $column, string $value1, string $value2) : self
    {
        $this->connectionInstance->whereBetween($column, $value1, $value2);

        return $this;
    }
    public function orWhereBetween(string $column, string $value1, string $value2) : self
    {
        $this->connectionInstance->orWhereBetween($column, $value1, $value2);

        return $this;
    }
    public function whereNotBetween(string $column, string $value1, string $value2) : self
    {
        $this->connectionInstance->whereNotBetween($column, $value1, $value2);

        return $this;
    }
    public function orWhereNotBetween(string $column, string $value1, string $value2) : self
    {
        $this->connectionInstance->orWhereNotBetween($column, $value1, $value2);

        return $this;
    }
    public function whereBetweenColumns(string $column, array $columns) : self
    {
        $this->connectionInstance->whereBetweenColumns($column, $columns);

        return $this;
    }
    public function orWhereBetweenColumns(string $column, array $columns) : self
    {
        $this->connectionInstance->orWhereBetweenColumns($column, $columns);

        return $this;
    }
    public function whereNull(string $column) : self
    {
        $this->connectionInstance->whereNull($column);

        return $this;
    }
    public function orWhereNull(string $column) : self
    {
        $this->connectionInstance->orWhereNull($column);

        return $this;
    }
    public function whereNotNull(string $column) : self
    {
        $this->connectionInstance->whereNotNull($column);

        return $this;
    }
    public function orWhereNotNull(string $column) : self
    {
        $this->connectionInstance->orWhereNotNull($column);

        return $this;
    }
    public function whereColumn(string $firstColumn, string $operator, string $secondColumn) : self
    {
        $this->connectionInstance->whereColumn($firstColumn, $operator, $secondColumn);

        return $this;
    }
    public function orWhereColumn(string $firstColumn, string $operator, string $secondColumn) : self
    {
        $this->connectionInstance->orWhereColumn($firstColumn, $operator, $secondColumn);

        return $this;
    }
    public function whereExists(QueryBuilderInterface $builder) : self
    {
        $this->connectionInstance->whereExists($builder);

        return $this;
    }
    public function get() : array
    {
        return $this->connectionInstance->get();
    }
    public function first() : mixed
    {
       return $this->connectionInstance->first();
    }
    public function groupBy(string ...$columns) : self
    {
        $this->connectionInstance->groupBy(...$columns);

        return $this;
    }
    public function orderBy(string $column, string $direction = 'ASC') : self
    {
        $this->connectionInstance->orderBy($column, $direction);

        return $this;
    }
    public function having(string $column, string $operator, string $value) : self
    {
        $this->connectionInstance->having($column, $operator, $value);

        return $this;
    }
    public function havingIn(string $column, array $values) : self
    {
        $this->connectionInstance->havingIn($column, $values);

        return $this;
    }
    public function orHavingIn(string $column, array $values) : self
    {
        $this->connectionInstance->orHavingIn($column, $values);

        return $this;
    }
    public function havingNotIn(string $column, array $values) : self
    {
        $this->connectionInstance->havingNotIn($column, $values);

        return $this;
    }
    public function orHavingNotIn(string $column, array $values) : self
    {
        $this->connectionInstance->orHavingNotIn($column, $values);

        return $this;
    }
    public function havingBetween(string $column, string $value1, string $value2) : self
    {
        $this->connectionInstance->havingBetween($column, $value1, $value2);

        return $this;
    }
    public function havingNotBetween(string $column, string $value1, string $value2) : self
    {
        $this->connectionInstance->havingNotBetween($column, $value1, $value2);

        return $this;
    }
    public function orHavingBetween(string $column, string $value1, string $value2): self
    {
        $this->connectionInstance->orHavingBetween($column, $value1, $value2);

        return $this;
    }
    public function orHavingNotBetween(string $column, string $value1, string $value2): self
    {
        $this->connectionInstance->orHavingNotBetween($column, $value1, $value2);

        return $this;
    }
    public function orHaving(string $column, string $operator, string $value) : self
    {
        $this->connectionInstance->orHaving($column, $operator, $value);

        return $this;
    }
    public function havingLike(string $column, string $value) : self
    {
        $this->connectionInstance->havingLike($column, $value);

        return $this;
    }
    public function orHavingLike(string $column, string $value) : self
    {
        $this->connectionInstance->orHavingLike($column, $value);

        return $this;
    }
    public function havingNotLike(string $column, string $value) : self
    {
        $this->connectionInstance->havingNotLike($column, $value);

        return $this;
    }
    public function orHavingNotLike(string $column, string $value) : self
    {
        $this->connectionInstance->orHavingNotLike($column, $value);

        return $this;
    }
    public function take(int $limit) : self
    {
        $this->connectionInstance->take($limit);

        return $this;
    }
    public function skip(int $offset) : self
    {
        $this->connectionInstance->skip($offset);

        return $this;
    }
    public function firstOrFail() : mixed
    {
        return $this->connectionInstance->firstOrFail();
    }
    public function increment(string $column, int $amount = 1) : bool
    {
        return $this->connectionInstance->increment($column, $amount);
    }
    public function decrement(string $column, int $amount = 1) : bool
    {
        return $this->connectionInstance->decrement($column, $amount);
    }
    public function count(string $column = '*') : int
    {
        return $this->connectionInstance->count($column);
    }
    public function max(string $column) : int
    {
        return $this->connectionInstance->max($column);
    }
    public function min(string $column) : int
    {
        return $this->connectionInstance->min($column);
    }
    public function sum(string $column) : int
    {
        return $this->connectionInstance->sum($column);
    }
    public function avg(string $column) : int
    {
        return $this->connectionInstance->avg($column);
    }
    public function value(string $column) : mixed
    {
        return $this->connectionInstance->value($column);
    }
    public function exists() : bool
    {
        return $this->connectionInstance->exists();
    }
    public function empty() : bool
    {
       return $this->connectionInstance->empty();
    }
    public function insert(array $data) : bool
    {
       return $this->connectionInstance->insert($data);
    }
    public function update(array $data) : bool
    {
        $this->connectionInstance->update($data);

        return $this;
    }
    public function upsert(array $data) : bool
    {
        return $this->connectionInstance->upsert($data);
    }
    public function delete() : bool
    {
        $this->connectionInstance->delete();

        return $this;
    }
    public function truncate() : bool
    {
        return $this->connectionInstance->truncate();
    }
    public function paginate(int $perPage) : array
    {
        return $this->connectionInstance->paginate($perPage);
    }
    public function with($instance,...$relations) : QueryBuilderInterface
    {
        return $this->connectionInstance->with($instance,...$relations);
    }

    function getQuery(): string
    {
        return '';
    }

    function getParameters(): array
    {
        return [];
    }
}