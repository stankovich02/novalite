<?php

namespace NovaLite\Database\Query\Connections;

use NovaLite\Application;
use NovaLite\Database\Model;
use NovaLite\Database\Query\Join;
use NovaLite\Database\Query\QueryBuilderInterface;
use NovaLite\Exceptions\ModelNotFoundException;

class MySQL implements QueryBuilderInterface
{
    private string $query;
    private string $table;
    private $instance = null;
    private array $parameters = [];
    protected bool $isSubBuilder = false;
    private array $relations = [];

    public function __construct(string $table,bool $isSubBuilder = false)
    {
        $this->table = $table;
        $this->isSubBuilder = $isSubBuilder;

        if (!$this->isSubBuilder) {
            $this->query = 'SELECT * FROM ' . $table;
        } else {
            $this->query = '';
        }

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
    public function whereGroup(callable $callback): self
    {
        $clause = str_contains($this->query, 'WHERE') ? ' AND (' : ' WHERE (';

        $subBuilder = new static($this->table, true);

        $callback($subBuilder);

        $subQuery = $subBuilder->query;

        $subQuery = preg_replace('/^SELECT \* FROM \w+/', '', $subQuery);

        $subQuery = preg_replace('/^\s*WHERE\s*/', '', $subQuery);

        $this->query .= $clause . $subQuery . ')';

        $this->parameters = array_merge($this->parameters, $subBuilder->parameters);

        return $this;
    }
    public function orWhereGroup(callable $callback): self
    {
        $clause = str_contains($this->query, 'WHERE') ? ' OR (' : ' WHERE (';

        $subBuilder = new static($this->table, true);

        $callback($subBuilder);

        $subQuery = $subBuilder->query;

        $subQuery = preg_replace('/^SELECT \* FROM \w+/', '', $subQuery);

        $subQuery = preg_replace('/^\s*WHERE\s*/', '', $subQuery);

        $this->query .= $clause . $subQuery . ')';

        $this->parameters = array_merge($this->parameters, $subBuilder->parameters);

        return $this;
    }

    public function where(string $column, string $operator, string|null $value) : self
    {
        $clause = ' AND';

        if (!$this->isSubBuilder) {
            $clause = !str_contains($this->query, 'WHERE') ? ' WHERE' : ' AND';
        } else {
            $clause = trim($this->query) === '' ? '' : ' AND';
        }

        $this->query .= "$clause $column $operator :$column";

        $this->parameters[":$column"] = $value;

        return $this;
    }
    public function orWhere(string $column, string $operator, string|null $value) : self
    {
        $this->query .= " OR $column $operator :$column";

        $this->parameters[":$column"] = $value;

        return $this;
    }
    public function whereNot(string $column, string $operator, string|null $value) : self
    {
        if(!str_contains($this->query, 'WHERE')) {
            $this->query .= " WHERE $column NOT $operator :$column";
        } else {
            $this->query .= " AND $column NOT $operator :$column";
        }

        $this->parameters[":$column"] = $value;

        return $this;
    }
    public function orWhereNot(string $column, string $operator, string|null $value) : self
    {
        $this->query .= " OR $column NOT $operator :$column";

        $this->parameters[":$column"] = $value;

        return $this;
    }
    public function whereIn(string $column, array $values): self
    {
        $clause = str_contains($this->query, 'WHERE') ? ' AND' : ' WHERE';
        $placeholders = [];

        foreach ($values as $key => $value) {
            $placeholder = ":{$column}_{$key}";
            $placeholders[] = $placeholder;
            $this->parameters[$placeholder] = $value;
        }

        $this->query .= "$clause $column IN (" . implode(', ', $placeholders) . ")";
        return $this;
    }

    public function orWhereIn(string $column, array $values) : self
    {
        $placeholders = [];

        foreach ($values as $key => $value) {
            $placeholder = ":{$column}_{$key}";
            $placeholders[] = $placeholder;
            $this->parameters[$placeholder] = $value;
        }

        $this->query .= " OR $column IN (" . implode(', ', $placeholders) . ")";
        return $this;
    }
    public function whereNotIn(string $column, array $values): self
    {
        $clause = str_contains($this->query, 'WHERE') ? ' AND' : ' WHERE';
        $placeholders = [];

        foreach ($values as $key => $value) {
            $placeholder = ":{$column}_{$key}";
            $placeholders[] = $placeholder;
            $this->parameters[$placeholder] = $value;
        }

        $this->query .= "$clause $column NOT IN (" . implode(', ', $placeholders) . ")";
        return $this;
    }

    public function orWhereNotIn(string $column, array $values) : self
    {
        $placeholders = [];

        foreach ($values as $key => $value) {
            $placeholder = ":{$column}_{$key}";
            $placeholders[] = $placeholder;
            $this->parameters[$placeholder] = $value;
        }

        $this->query .= " OR $column NOT IN (" . implode(', ', $placeholders) . ")";
        return $this;
    }
    public function whereAny(array $columns, string $operator, string|null $value): self
    {
        $clause = str_contains($this->query, 'WHERE') ? ' AND (' : ' WHERE (';
        $this->query .= $clause;

        $placeholders = [];
        foreach ($columns as $index => $column) {
            $placeholder = ":{$column}_{$index}";
            $placeholders[] = "$column $operator $placeholder";
            $this->parameters[$placeholder] = $value;
        }

        $this->query .= implode(' OR ', $placeholders) . ')';
        return $this;
    }
    public function whereAll(array $columns, string $operator, string|null $value) : self
    {

        $clause = str_contains($this->query, 'WHERE') ? ' AND (' : ' WHERE (';
        $this->query .= $clause;

        $placeholders = [];
        foreach ($columns as $index => $column) {
            $placeholder = ":{$column}_{$index}";
            $placeholders[] = "$column $operator $placeholder";
            $this->parameters[$placeholder] = $value;
        }

        $this->query .= implode(' AND ', $placeholders) . ')';
        return $this;
    }
    public function whereNone(array $columns, string $operator, string|null $value) : self
    {
        $clause = str_contains($this->query, 'WHERE') ? ' AND NOT (' : ' WHERE NOT (';
        $this->query .= $clause;

        $conditions = [];
        foreach ($columns as $key => $column) {
            $placeholder = ":{$column}_none_{$key}";
            $conditions[] = "$column $operator $placeholder";
            $this->parameters[$placeholder] = $value;
        }

        $this->query .= implode(' OR ', $conditions) . ')';
        return $this;
    }

    public function whereLike(string $column, string $value) : self
    {
        $clause = str_contains($this->query, 'WHERE') ? ' AND' : ' WHERE';
        $placeholder = ":{$column}_like";
        $this->query .= "$clause $column LIKE $placeholder";
        $this->parameters[$placeholder] = "%$value%";

        return $this;
    }

    public function orWhereLike(string $column, string $value) : self
    {
        $placeholder = ":{$column}_like";
        $this->query .= " OR $column LIKE $placeholder";
        $this->parameters[$placeholder] = "%$value%";

        return $this;
    }

    public function whereNotLike(string $column, string $value) : self
    {
        $clause = str_contains($this->query, 'WHERE') ? ' AND' : ' WHERE';
        $placeholder = ":{$column}_not_like";
        $this->query .= "$clause $column NOT LIKE $placeholder";
        $this->parameters[$placeholder] = "%$value%";

        return $this;
    }

    public function orWhereNotLike(string $column, string $value) : self
    {
        $placeholder = ":{$column}_not_like";
        $this->query .= " OR $column NOT LIKE $placeholder";
        $this->parameters[$placeholder] = "%$value%";

        return $this;
    }

    public function whereBetween(string $column, string $value1, string $value2) : self
    {
        $clause = str_contains($this->query, 'WHERE') ? ' AND' : ' WHERE';
        $placeholder1 = ":{$column}_between_1";
        $placeholder2 = ":{$column}_between_2";

        $this->query .= "$clause $column BETWEEN $placeholder1 AND $placeholder2";
        $this->parameters[$placeholder1] = $value1;
        $this->parameters[$placeholder2] = $value2;

        return $this;
    }

    public function orWhereBetween(string $column, string $value1, string $value2) : self
    {
        $placeholder1 = ":{$column}_between_1";
        $placeholder2 = ":{$column}_between_2";

        $this->query .= " OR $column BETWEEN $placeholder1 AND $placeholder2";
        $this->parameters[$placeholder1] = $value1;
        $this->parameters[$placeholder2] = $value2;

        return $this;
    }

    public function whereNotBetween(string $column, string $value1, string $value2) : self
    {
        $clause = str_contains($this->query, 'WHERE') ? ' AND' : ' WHERE';
        $placeholder1 = ":{$column}_not_between_1";
        $placeholder2 = ":{$column}_not_between_2";

        $this->query .= "$clause $column NOT BETWEEN $placeholder1 AND $placeholder2";
        $this->parameters[$placeholder1] = $value1;
        $this->parameters[$placeholder2] = $value2;

        return $this;
    }

    public function orWhereNotBetween(string $column, string $value1, string $value2) : self
    {
        $placeholder1 = ":{$column}_not_between_1";
        $placeholder2 = ":{$column}_not_between_2";

        $this->query .= " OR $column NOT BETWEEN $placeholder1 AND $placeholder2";
        $this->parameters[$placeholder1] = $value1;
        $this->parameters[$placeholder2] = $value2;

        return $this;
    }

    public function whereBetweenColumns(string $column, array $columns) : self
    {
        $clause = str_contains($this->query, 'WHERE') ? ' AND' : ' WHERE';
        $this->query .= "$clause $column BETWEEN $columns[0] AND $columns[1]";

        return $this;
    }

    public function orWhereBetweenColumns(string $column, array $columns) : self
    {
        $this->query .= " OR $column BETWEEN $columns[0] AND $columns[1]";

        return $this;
    }

    public function whereNull(string $column) : self
    {

        if (!$this->isSubBuilder) {
            $clause = !str_contains($this->query, 'WHERE') ? ' WHERE' : ' AND';
        } else {
            $clause = trim($this->query) === '' ? '' : ' AND';
        }

        $this->query .= "$clause $column IS NULL";

        return $this;
    }

    public function orWhereNull(string $column) : self
    {
        $this->query .= " OR $column IS NULL";

        return $this;
    }

    public function whereNotNull(string $column) : self
    {

        if (!$this->isSubBuilder) {
            $clause = !str_contains($this->query, 'WHERE') ? ' WHERE' : ' AND';
        } else {
            $clause = trim($this->query) === '' ? '' : ' AND';
        }

        $this->query .= "$clause $column IS NOT NULL";

        return $this;
    }

    public function orWhereNotNull(string $column) : self
    {
        $this->query .= " OR $column IS NOT NULL";

        return $this;
    }

    public function whereColumn(string $firstColumn, string $operator, string $secondColumn) : self
    {
        $clause = str_contains($this->query, 'WHERE') ? ' AND' : ' WHERE';
        $this->query .= "$clause $firstColumn $operator $secondColumn";

        return $this;
    }

    public function orWhereColumn(string $firstColumn, string $operator, string $secondColumn) : self
    {
        $this->query .= " OR $firstColumn $operator $secondColumn";

        return $this;
    }
    public function whereExists(QueryBuilderInterface $builder): self
    {
        $clause = str_contains($this->query, 'WHERE') ? ' AND' : ' WHERE';
        $this->query .= "$clause EXISTS (" . $builder->getQuery() . ")";
        $this->parameters = array_merge($this->parameters, $builder->getParameters());
        return $this;
    }
    public function get() : array
    {
        var_dump($this->query);
        exit;
        $statement = Application::$app->db->prepare($this->query);
        $statement->execute($this->parameters);
        $rows = $statement->fetchAll();
        if($this->instance === null) {
            return $rows;
        }
        if(count($rows) === 0) {
            return [];
        }
        $instances = [];
        foreach ($rows as $row) {
            $instance = new $this->instance();
            $instance->exists = true;
            $primaryKey = $instance->primaryKey ?? 'id';
            $instance->{$primaryKey} = $row->{$primaryKey} ?? null;
            foreach ($row as $key => $value) {
                $instance->{$key} = $value;
            }

            if (!empty($this->relations)) {
                foreach ($this->relations as $relation) {
                    if(str_contains($relation, '.')) {
                        $relations = explode('.', $relation);
                        $relationInstance = $instance->{$relations[0]};
                        if($relationInstance){
                            if(is_array($relationInstance)) {
                                foreach ($relationInstance as $relInst) {
                                    $relInst->{$relations[1]} = $relInst->{$relations[1]}()->getResults();
                                }
                            }
                            else{
                                $relationInstance->{$relations[1]} = $relationInstance->{$relations[1]}()->getResults();
                            }
                        }
                    }
                    else{
                        if (method_exists($instance, $relation)) {
                            $instance->{$relation} = $instance->{$relation}()->getResults();
                        }
                    }
                }
            }
            $instances[] = $instance;
        }

        return $instances;
    }
    public function first() : mixed
    {
        $statement = Application::$app->db->prepare($this->query);
        $statement->execute($this->parameters);
        $row = $statement->fetch();
        if($this->instance === null) {
            return $row;
        }
        if(!$row) {
            return null;
        }
        $instance = new $this->instance();
        $instance->exists = true;
        $primaryKey = $instance->primaryKey ?? 'id';
        $instance->{$primaryKey} = $row->{$primaryKey} ?? null;
        foreach ($row as $key => $value) {
            $instance->{$key} = $value;
        }
        if (!empty($this->relations)) {
            foreach ($this->relations as $relation) {
                if(str_contains($relation, '.')) {
                    $relations = explode('.', $relation);
                    $relationInstance = $instance->{$relations[0]};
                    if($relationInstance){
                        if(is_array($relationInstance)) {
                            foreach ($relationInstance as $relInst) {
                                $relInst->{$relations[1]} = $relInst->{$relations[1]}()->getResults();
                            }
                        }
                        else{
                            $relationInstance->{$relations[1]} = $relationInstance->{$relations[1]}()->getResults();
                        }
                    }
                }
                else{
                    if (method_exists($instance, $relation)) {
                        $instance->{$relation} = $instance->{$relation}()->getResults();
                    }
                }
            }
        }
        return $instance;
    }
    public function find($instance, $id) : \stdClass|Model|null
    {
        $this->instance = $instance;
        $primaryKey = $instance->primaryKey ?? 'id';
        $this->query = "SELECT * FROM $this->table WHERE $primaryKey = :" . $primaryKey;
        $this->parameters = [":" . $primaryKey => $id];
        return $this->first();
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
        if(!str_contains($this->query, 'ORDER BY')) {
            $this->query .= " ORDER BY $column $direction";
        } else {
            $this->query .= ", $column $direction";
        }

        return $this;
    }
    public function having(string $column, string $operator, string $value) : self
    {
        $clause = str_contains($this->query, 'HAVING') ? ' AND' : ' HAVING';
        $placeholder = ":{$column}_having";
        $this->query .= "$clause $column $operator $placeholder";
        $this->parameters[$placeholder] = $value;

        return $this;
    }

    public function orHaving(string $column, string $operator, string $value) : self
    {
        $placeholder = ":{$column}_or_having";
        $this->query .= " OR $column $operator $placeholder";
        $this->parameters[$placeholder] = $value;

        return $this;
    }

    public function havingIn(string $column, array $values) : self
    {
        $clause = str_contains($this->query, 'HAVING') ? ' AND' : ' HAVING';
        $placeholders = [];

        foreach ($values as $key => $value) {
            $placeholder = ":{$column}_in_{$key}";
            $placeholders[] = $placeholder;
            $this->parameters[$placeholder] = $value;
        }

        $this->query .= "$clause $column IN (" . implode(', ', $placeholders) . ")";
        return $this;
    }

    public function orHavingIn(string $column, array $values) : self
    {
        $placeholders = [];

        foreach ($values as $key => $value) {
            $placeholder = ":{$column}_or_in_{$key}";
            $placeholders[] = $placeholder;
            $this->parameters[$placeholder] = $value;
        }

        $this->query .= " OR $column IN (" . implode(', ', $placeholders) . ")";
        return $this;
    }

    public function havingNotIn(string $column, array $values) : self
    {
        $clause = str_contains($this->query, 'HAVING') ? ' AND' : ' HAVING';
        $placeholders = [];

        foreach ($values as $key => $value) {
            $placeholder = ":{$column}_not_in_{$key}";
            $placeholders[] = $placeholder;
            $this->parameters[$placeholder] = $value;
        }

        $this->query .= "$clause $column NOT IN (" . implode(', ', $placeholders) . ")";
        return $this;
    }

    public function orHavingNotIn(string $column, array $values) : self
    {
        $placeholders = [];

        foreach ($values as $key => $value) {
            $placeholder = ":{$column}_or_not_in_{$key}";
            $placeholders[] = $placeholder;
            $this->parameters[$placeholder] = $value;
        }

        $this->query .= " OR $column NOT IN (" . implode(', ', $placeholders) . ")";
        return $this;
    }

    public function havingBetween(string $column, string $value1, string $value2) : self
    {
        $clause = str_contains($this->query, 'HAVING') ? ' AND' : ' HAVING';
        $placeholder1 = ":{$column}_between_1";
        $placeholder2 = ":{$column}_between_2";

        $this->query .= "$clause $column BETWEEN $placeholder1 AND $placeholder2";
        $this->parameters[$placeholder1] = $value1;
        $this->parameters[$placeholder2] = $value2;

        return $this;
    }

    public function havingNotBetween(string $column, string $value1, string $value2) : self
    {
        $clause = str_contains($this->query, 'HAVING') ? ' AND' : ' HAVING';
        $placeholder1 = ":{$column}_not_between_1";
        $placeholder2 = ":{$column}_not_between_2";

        $this->query .= "$clause $column NOT BETWEEN $placeholder1 AND $placeholder2";
        $this->parameters[$placeholder1] = $value1;
        $this->parameters[$placeholder2] = $value2;

        return $this;
    }

    public function orHavingBetween(string $column, string $value1, string $value2): self
    {
        $placeholder1 = ":{$column}_or_between_1";
        $placeholder2 = ":{$column}_or_between_2";

        $this->query .= " OR $column BETWEEN $placeholder1 AND $placeholder2";
        $this->parameters[$placeholder1] = $value1;
        $this->parameters[$placeholder2] = $value2;

        return $this;
    }

    public function orHavingNotBetween(string $column, string $value1, string $value2): self
    {
        $placeholder1 = ":{$column}_or_not_between_1";
        $placeholder2 = ":{$column}_or_not_between_2";

        $this->query .= " OR $column NOT BETWEEN $placeholder1 AND $placeholder2";
        $this->parameters[$placeholder1] = $value1;
        $this->parameters[$placeholder2] = $value2;

        return $this;
    }

    public function havingLike(string $column, string $value) : self
    {
        $clause = str_contains($this->query, 'HAVING') ? ' AND' : ' HAVING';
        $placeholder = ":{$column}_like";
        $this->query .= "$clause $column LIKE $placeholder";
        $this->parameters[$placeholder] = "%$value%";

        return $this;
    }

    public function orHavingLike(string $column, string $value) : self
    {
        $placeholder = ":{$column}_or_like";
        $this->query .= " OR $column LIKE $placeholder";
        $this->parameters[$placeholder] = "%$value%";

        return $this;
    }

    public function havingNotLike(string $column, string $value) : self
    {
        $clause = str_contains($this->query, 'HAVING') ? ' AND' : ' HAVING';
        $placeholder = ":{$column}_not_like";
        $this->query .= "$clause $column NOT LIKE $placeholder";
        $this->parameters[$placeholder] = "%$value%";

        return $this;
    }

    public function orHavingNotLike(string $column, string $value) : self
    {
        $placeholder = ":{$column}_or_not_like";
        $this->query .= " OR $column NOT LIKE $placeholder";
        $this->parameters[$placeholder] = "%$value%";

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
        $statement->execute($this->parameters);
        return $statement->fetchColumn();
    }
    public function max(string $column) : int
    {
        $this->query = str_replace('*', "MAX($column)", $this->query);
        $statement = Application::$app->db->prepare($this->query);
        $statement->execute($this->parameters);
        return $statement->fetchColumn();
    }
    public function min(string $column) : int
    {
        $this->query = str_replace('*', "MIN($column)", $this->query);
        $statement = Application::$app->db->prepare($this->query);
        $statement->execute($this->parameters);
        return $statement->fetchColumn();
    }
    public function sum(string $column) : int
    {
        $this->query = str_replace('*', "SUM($column)", $this->query);
        $statement = Application::$app->db->prepare($this->query);
        $statement->execute($this->parameters);
        return $statement->fetchColumn();
    }
    public function avg(string $column) : int
    {
        $this->query = str_replace('*', "AVG($column)", $this->query);
        $statement = Application::$app->db->prepare($this->query);
        $statement->execute($this->parameters);
        return $statement->fetchColumn();
    }
    public function value(string $column) : mixed
    {
        $this->query = str_replace('*', $column, $this->query);
        $statement = Application::$app->db->prepare($this->query);
        $statement->execute($this->parameters);
        return $statement->fetchColumn();
    }
    public function exists() : bool
    {
        $statement = Application::$app->db->prepare($this->query);
        $statement->execute($this->parameters);
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
    public function with($instance,...$relations) : self
    {
        $this->instance = $instance;
        $this->relations = $relations;

        return $this;
    }
    public function getQuery() : string
    {
        return $this->query;
    }
    public function getParameters() : array
    {
        return $this->parameters;
    }

}
