<?php

namespace NovaLite\Database\Query;

interface QueryBuilderInterface
{
    function setInstance($instance) : void;
    function select(string ...$columns) : self;
    function distinct() : self;
    function join(string $table, string|\Closure $first, string $operator, string $second) : self;
    function leftJoin(string $table, string|\Closure $first, string $operator, string $second) : self;
    function rightJoin(string $table, string|\Closure $first, string $operator, string $second) : self;
    function where(string $column, string $operator, string $value) : self;
    function orWhere(string $column, string $operator, string $value) : self;
    function whereNot(string $column, string $operator, string $value) : self;
    function orWhereNot(string $column, string $operator, string $value) : self;
    function whereIn(string $column, array $values) : self;
    function orWhereIn(string $column, array $values) : self;
    function whereNotIn(string $column, array $values) : self;
    function orWhereNotIn(string $column, array $values) : self;
    function whereAny(array $columns, string $operator, string $value) : self;
    function whereAll(array $columns, string $operator, string $value) : self;
    function whereNone(array $columns, string $operator, string $value) : self;
    function whereLike(string $column, string $value) : self;
    function orWhereLike(string $column, string $value) : self;
    function whereNotLike(string $column, string $value) : self;
    function orWhereNotLike(string $column, string $value) : self;
    function whereBetween(string $column, string $value1, string $value2) : self;
    function orWhereBetween(string $column, string $value1, string $value2) : self;
    function whereNotBetween(string $column, string $value1, string $value2) : self;
    function orWhereNotBetween(string $column, string $value1, string $value2) : self;
    function whereBetweenColumns(string $column, array $columns) : self;
    function orWhereBetweenColumns(string $column, array $columns) : self;
    function whereNull(string $column) : self;
    function orWhereNull(string $column) : self;
    function whereNotNull(string $column) : self;
    function orWhereNotNull(string $column) : self;
    function whereColumn(string $firstColumn, string $operator, string $secondColumn) : self;
    function orWhereColumn(string $firstColumn, string $operator, string $secondColumn) : self;
    function whereExists(QueryBuilderInterface $builder) : self;
    function groupBy(string ...$columns) : self;
    function orderBy(string $column, string $direction = 'ASC') : self;
    function having(string $column, string $operator, string $value) : self;
    function orHaving(string $column, string $operator, string $value) : self;
    function havingIn(string $column, array $values) : self;
    function orHavingIn(string $column, array $values) : self;
    function havingNotIn(string $column, array $values) : self;
    function orHavingNotIn(string $column, array $values) : self;
    function havingBetween(string $column, string $value1, string $value2) : self;
    function orHavingBetween(string $column, string $value1, string $value2) : self;
    function havingNotBetween(string $column, string $value1, string $value2) : self;
    function orHavingNotBetween(string $column, string $value1, string $value2) : self;
    function havingLike(string $column, string $value) : self;
    function orHavingLike(string $column, string $value) : self;
    function havingNotLike(string $column, string $value) : self;
    function orHavingNotLike(string $column, string $value) : self;
    function take(int $limit) : self;
    function skip(int $offset) : self;
    function increment(string $column, int $amount = 1) : bool;
    function decrement(string $column, int $amount = 1) : bool;
    function count(string $column = '*') : int;
    function sum(string $column) : int;
    function avg(string $column) : int;
    function min(string $column) : int;
    function max(string $column) : int;
    function value(string $column) : mixed;
    function exists() : bool;
    function empty() : bool;
    function insert(array $data) : bool;
    function update(array $data) : bool;
    function delete() : bool;
    function upsert(array $data) : bool;
    function truncate() : bool;
    function paginate(int $perPage): array;
    function with($instance,...$relations) : array;
    function get() : array;
    function first() : mixed;
    function getQuery() : string;
    function getParameters() : array;


}