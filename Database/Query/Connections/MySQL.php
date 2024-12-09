<?php

namespace NovaLite\Database\Query\Connections;

use NovaLite\Application;
use NovaLite\Database\Query\Join;
use NovaLite\Database\Query\QueryBuilderInterface;
use NovaLite\Exceptions\ModelNotFoundException;

class MySQL implements QueryBuilderInterface
{
    private string $query;
    private string $table;
    private $instance = null;
    private array $parameters = [];

    public function __construct(string $table)
    {
        $this->table = $table;
        $this->query = "SELECT * FROM $table";
    }
    public function setInstance($instance): void
    {
        $this->instance = $instance;
    }
    public function select(string ...$columns) : self
    {
        $columns = implode(', ', $columns);
        $this->query = str_replace('*', $columns, $this->query);

        return $this;
    }
    public function distinct() : self
    {
        $this->query = str_replace('SELECT', 'SELECT DISTINCT', $this->query);

        return $this;
    }
    //testirati Closure pored standardnog Join-a
    public function join(string $table, string|\Closure $first, string $operator, string $second) : self
    {
        if($first instanceof \Closure) {
            $this->query .= " JOIN $table ON ";
            $this->query = $first(new Join($this->query));
        } else {
            $this->query .= " JOIN $table ON $first $operator $second";
        }

        return $this;
    }
    public function leftJoin(string $table, string|\Closure $first, string $operator, string $second) : self
    {
        $this->query .= " LEFT JOIN $table ON $first $operator $second";

        return $this;
    }
    public function rightJoin(string $table, string|\Closure $first, string $operator, string $second) : self
    {
        $this->query .= " RIGHT JOIN $table ON $first $operator $second";

        return $this;
    }
    //testirati dva where spojena
    public function where(string $column, string $operator, string $value) : self
    {
        $clause = str_contains($this->query, 'WHERE') ? ' AND' : ' WHERE';
        $this->query .= "$clause $column $operator :$column";

        $this->parameters[":$column"] = $value;

        return $this;
    }
    public function orWhere(string $column, string $operator, string $value) : self
    {
        $this->query .= " OR $column $operator :$column";

        $this->parameters[":$column"] = $value;

        return $this;
    }
    public function whereNot(string $column, string $operator, string $value) : self
    {
        $this->query .= " WHERE $column NOT $operator :$column";

        $this->parameters[":$column"] = $value;

        return $this;
    }
    public function orWhereNot(string $column, string $operator, string $value) : self
    {
        $this->query .= " OR $column NOT $operator :$column";

        $this->parameters[":$column"] = $value;

        return $this;
    }
    public function whereIn(string $column, array $values) : self
    {
        $values = implode(', ', $values);
        $this->query .= " WHERE $column IN ($values)";

        return $this;
    }
    public function orWhereIn(string $column, array $values) : self
    {
        $values = implode(', ', $values);
        $this->query .= " OR $column IN ($values)";

        return $this;
    }
    public function whereNotIn(string $column, array $values) : self
    {
        $values = implode(', ', $values);
        $this->query .= " WHERE $column NOT IN ($values)";

        return $this;
    }
    public function orWhereNotIn(string $column, array $values) : self
    {
        $values = implode(', ', $values);
        $this->query .= " OR $column NOT IN ($values)";

        return $this;
    }
    public function whereAny(array $columns, string $operator, string $value) : self
    {
        $this->query .= " WHERE ";
        foreach ($columns as $column) {
            $this->query .= "$column $operator '$value' OR ";
        }
        $this->query = rtrim($this->query, ' OR ');

        return $this;
    }
    public function whereAll(array $columns, string $operator, string $value) : self
    {
        $this->query .= " WHERE ";
        foreach ($columns as $column) {
            $this->query .= "$column $operator '$value' AND ";
        }
        $this->query = rtrim($this->query, ' AND ');

        return $this;
    }
    public function whereNone(array $columns, string $operator, string $value) : self
    {
        $this->query .= " WHERE NOT ";
        foreach ($columns as $column) {
            $this->query .= "$column $operator '$value' OR ";
        }
        $this->query = rtrim($this->query, ' OR ');

        return $this;
    }
    public function whereLike(string $column, string $value) : self
    {
        $this->query .= " WHERE $column LIKE '%$value%'";

        return $this;
    }
    public function orWhereLike(string $column, string $value) : self
    {
        $this->query .= " OR $column LIKE '%$value%'";

        return $this;
    }
    public function whereNotLike(string $column, string $value) : self
    {
        $this->query .= " WHERE $column NOT LIKE '%$value%'";

        return $this;
    }
    public function orWhereNotLike(string $column, string $value) : self
    {
        $this->query .= " OR $column NOT LIKE '%$value%'";

        return $this;
    }
    public function whereBetween(string $column, string $value1, string $value2) : self
    {
        $this->query .= " WHERE $column BETWEEN '$value1' AND '$value2'";

        return $this;
    }
    public function orWhereBetween(string $column, string $value1, string $value2) : self
    {
        $this->query .= " OR $column BETWEEN '$value1' AND '$value2'";

        return $this;
    }
    public function whereNotBetween(string $column, string $value1, string $value2) : self
    {
        $this->query .= " WHERE $column NOT BETWEEN '$value1' AND '$value2'";

        return $this;
    }
    public function orWhereNotBetween(string $column, string $value1, string $value2) : self
    {
        $this->query .= " OR $column NOT BETWEEN '$value1' AND '$value2'";

        return $this;
    }
    public function whereBetweenColumns(string $column, array $columns) : self
    {
        $this->query .= " WHERE $column BETWEEN $columns[0] AND $columns[1]";

        return $this;
    }
    public function orWhereBetweenColumns(string $column, array $columns) : self
    {
        $this->query .= " OR $column BETWEEN $columns[0] AND $columns[1]";

        return $this;
    }
    public function whereNull(string $column) : self
    {
        $this->query .= " WHERE $column IS NULL";

        return $this;
    }
    public function orWhereNull(string $column) : self
    {
        $this->query .= " OR $column IS NULL";

        return $this;
    }
    public function whereNotNull(string $column) : self
    {
        $this->query .= " WHERE $column IS NOT NULL";

        return $this;
    }
    public function orWhereNotNull(string $column) : self
    {
        $this->query .= " OR $column IS NOT NULL";

        return $this;
    }
    public function whereColumn(string $firstColumn, string $operator, string $secondColumn) : self
    {
        $this->query .= " WHERE $firstColumn $operator $secondColumn";

        return $this;
    }
    public function orWhereColumn(string $firstColumn, string $operator, string $secondColumn) : self
    {
        $this->query .= " OR $firstColumn $operator $secondColumn";

        return $this;
    }
    public function whereExists(QueryBuilderInterface $builder) : self
    {
        $this->query .= " WHERE EXISTS (" . $builder->query . ")";

        return $this;
    }
    public function get() : array
    {
        $statement = Application::$app->db->prepare($this->query);
        $statement->execute();
        $rows = $statement->fetchAll();
        if($this->instance === null) {
            return $rows;
        }
        $instances = [];
        foreach ($rows as $row) {
            $instance = new $this->instance();
            foreach ($row as $key => $value) {
                $instance->{$key} = $value;
            }
            $instances[] = $instance;
        }
        return $instances;
    }
    public function first() : mixed
    {
        $statement = Application::$app->db->prepare($this->query);
        $statement->execute();
        $row = $statement->fetch();
        $instance = new $this->instance();
        foreach ($row as $key => $value) {
            $instance->{$key} = $value;
        }
        return $instance;
    }
    public function groupBy(string ...$columns) : self
    {
        $columns = implode(', ', $columns);
        $this->query .= " GROUP BY $columns";

        return $this;
    }
    public function orderBy(string $column, string $direction = 'ASC') : self
    {
        $direction = strtoupper($direction);
        $this->query .= " ORDER BY $column $direction";

        return $this;
    }
    public function having(string $column, string $operator, string $value) : self
    {
        $this->query .= " HAVING $column $operator '$value'";

        return $this;
    }
    public function havingIn(string $column, array $values) : self
    {
        $values = implode(', ', $values);
        $this->query .= " HAVING $column IN ($values)";

        return $this;
    }
    public function orHavingIn(string $column, array $values) : self
    {
        $values = implode(', ', $values);
        $this->query .= " OR $column IN ($values)";

        return $this;
    }
    public function havingNotIn(string $column, array $values) : self
    {
        $values = implode(', ', $values);
        $this->query .= " HAVING $column NOT IN ($values)";

        return $this;
    }
    public function orHavingNotIn(string $column, array $values) : self
    {
        $values = implode(', ', $values);
        $this->query .= " OR $column NOT IN ($values)";

        return $this;
    }
    public function havingBetween(string $column, string $value1, string $value2) : self
    {
        $this->query .= " HAVING $column BETWEEN '$value1' AND '$value2'";

        return $this;
    }
    public function havingNotBetween(string $column, string $value1, string $value2) : self
    {
        $this->query .= " HAVING $column NOT BETWEEN '$value1' AND '$value2'";

        return $this;
    }
    public function orHavingBetween(string $column, string $value1, string $value2): QueryBuilderInterface
    {
        $this->query .= " OR $column BETWEEN '$value1' AND '$value2'";

        return $this;
    }
    public function orHavingNotBetween(string $column, string $value1, string $value2): QueryBuilderInterface
    {
        $this->query .= " OR $column NOT BETWEEN '$value1' AND '$value2'";

        return $this;
    }
    public function orHaving(string $column, string $operator, string $value) : self
    {
        $this->query .= " OR $column $operator '$value'";

        return $this;
    }
    public function havingLike(string $column, string $value) : self
    {
        $this->query .= " HAVING $column LIKE '%$value%'";

        return $this;
    }
    public function orHavingLike(string $column, string $value) : self
    {
        $this->query .= " OR $column LIKE '%$value%'";

        return $this;
    }
    public function havingNotLike(string $column, string $value) : self
    {
        $this->query .= " HAVING $column NOT LIKE '%$value%'";

        return $this;
    }
    public function orHavingNotLike(string $column, string $value) : self
    {
        $this->query .= " OR $column NOT LIKE '%$value%'";

        return $this;
    }
    public function take(int $limit) : self
    {
        $this->query .= " LIMIT $limit";

        return $this;
    }
    public function skip(int $offset) : self
    {
        $this->query .= " OFFSET $offset";

        return $this;
    }
    public function firstOrFail()
    {
        $instance = $this->first();
        if(!$instance) {
            throw new ModelNotFoundException();
        }
        return $instance;
    }
    public function increment(string $column, int $amount = 1) : bool
    {
        $this->query = "UPDATE $this->table SET $column = $column + $amount";
        return Application::$app->db->exec($this->query);
    }
    public function decrement(string $column, int $amount = 1) : bool
    {
        $this->query = "UPDATE $this->table SET $column = $column - $amount";
        return Application::$app->db->exec($this->query);
    }
    public function count(string $column = '*') : int
    {
        $this->query = str_replace('*', "COUNT($column)", $this->query);
        $statement = Application::$app->db->prepare($this->query);
        $statement->execute();
        return $statement->fetchColumn();
    }
    public function max(string $column) : int
    {
        $this->query = str_replace('*', "MAX($column)", $this->query);
        $statement = Application::$app->db->prepare($this->query);
        $statement->execute();
        return $statement->fetchColumn();
    }
    public function min(string $column) : int
    {
        $this->query = str_replace('*', "MIN($column)", $this->query);
        $statement = Application::$app->db->prepare($this->query);
        $statement->execute();
        return $statement->fetchColumn();
    }
    public function sum(string $column) : int
    {
        $this->query = str_replace('*', "SUM($column)", $this->query);
        $statement = Application::$app->db->prepare($this->query);
        $statement->execute();
        return $statement->fetchColumn();
    }
    public function avg(string $column) : int
    {
        $this->query = str_replace('*', "AVG($column)", $this->query);
        $statement = Application::$app->db->prepare($this->query);
        $statement->execute();
        return $statement->fetchColumn();
    }
    public function value(string $column) : mixed
    {
        $this->query = str_replace('*', $column, $this->query);
        $statement = Application::$app->db->prepare($this->query);
        $statement->execute();
        return $statement->fetchColumn();
    }
    public function exists() : bool
    {
        $statement = Application::$app->db->prepare($this->query);
        $statement->execute();
        return $statement->rowCount() > 0;
    }
    public function empty() : bool
    {
        $this->query = "DELETE FROM $this->table";
        return Application::$app->db->exec($this->query);
    }
    public function insert(array $data) : bool
    {
        $columns = implode(', ', array_keys($data));
        $values = implode("', '", array_values($data));
        $this->query = "INSERT INTO $this->table ($columns) VALUES ('$values')";
        return Application::$app->db->exec($this->query);
    }
    public function update(array $data) : bool
    {
        $set = '';
        foreach ($data as $key => $value) {
            $set .= "$key = ':$key', ";
            $this->parameters[":$key"] = $value;
        }
        $set = rtrim($set, ', ');
        $this->query = str_replace("SELECT * FROM $this->table", "UPDATE $this->table SET $set", $this->query);
        $statement = Application::$app->db->prepare($this->query);
        return $statement->execute($this->parameters);
    }
    public function upsert(array $data) : bool
    {
        $columns = implode(', ', array_keys($data));
        $values = implode("', '", array_values($data));
        $this->query = "INSERT INTO $this->table ($columns) VALUES ('$values') ON DUPLICATE KEY UPDATE $columns = '$values'";
        return Application::$app->db->exec($this->query);
    }
    public function delete() : bool
    {
        $this->query = str_replace('SELECT *', 'DELETE', $this->query);
        $statement = Application::$app->db->prepare($this->query);
        return $statement->execute($this->parameters);
    }
    public function truncate() : bool
    {
        $this->query = "TRUNCATE TABLE $this->table";
        return Application::$app->db->exec($this->query);
    }
    public function paginate(int $perPage) : array
    {
        $page = $_GET['page'] ?? 1;
        $offset = ($page - 1) * $perPage;
        $this->query .= " LIMIT $perPage OFFSET $offset";
        return [
            'total' => $this->count(),
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($this->count() / $perPage),
            'data' => $this->get()
        ];
    }
    public function with($instance,...$relations) : array
    {
        $this->instance = $instance;
        $rows = $this->get();
        foreach ($relations as $relation) {
            foreach ($rows as $row) {
                $row->{$relation} = $row->{$relation}()->getResults();
            }
        }
        return $rows;
    }
}
