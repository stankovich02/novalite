<?php

namespace NovaLite\Database;

use NovaLite\Database\Query\Builder;
use NovaLite\Database\Query\QueryBuilderInterface;
use NovaLite\Exceptions\ModelNotFoundException;

abstract class Model
{
    use Relationships,TableGuess;
    protected string $table;
    protected array $attributes = [];
    protected array $fillable = [];
    protected string $primaryKey = 'id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected array $hidden = [];

    public bool $exists = false;
    protected bool $timestamps = true;
    protected static \PDO $pdo;

    public static function setConnection($pdoInstance) : void
    {
        self::$pdo = $pdoInstance;
    }

    public static function find($id) : static
    {
        $instance = new static();
        $table = $instance->guessTableName($instance);
        $sql = "SELECT * FROM " . $table . " WHERE {$instance->primaryKey} = :id";
        $statement = self::$pdo->prepare($sql);
        $statement->bindValue(':id', $id);
        $statement->execute();
        $data =  $statement->fetch();
        if($data) {
            foreach ($data as $key => $value) {
                $instance->{$key} = $value;
            }
            $instance->exists = true;
        }
        $instance->hideHiddenFields($instance);
        return $instance;
    }

    private function hideHiddenFields($instance): void {
        $attributes = $instance->attributes;

        foreach ($this->hidden as $hiddenField) {
            unset($attributes[$hiddenField]);
        }

        $instance->attributes = $attributes;
    }

    public static function all() : array
    {
        $instance = new static();
        $table = $instance->guessTableName($instance);
        $sql = "SELECT * FROM " . $table;
        $statement = self::$pdo->query($sql);
        $rows =  $statement->fetchAll();
        if($rows)
        {
            return $instance->makeInstances($rows);
        }
        return [];
    }
    public static function where(string $column,string $operator ,$value) : QueryBuilderInterface
    {
        $instance = new static();
        $table = $instance->guessTableName($instance);
        $builder = new Builder($table);
        $builder->setInstance($instance);
        return $builder->where($column,$operator, $value);
    }
    public static function findOrFail($id) : static
    {
        $instance = self::find($id);
        if(!$instance->exists) {
            throw new ModelNotFoundException();
        }
        return $instance;
    }
    public static function create(array $data) : bool
    {
        $instance = new static();
        $table = $instance->guessTableName($instance);
        if($instance->fillable) {
            $data = array_filter($data, fn($key) => in_array($key, $instance->fillable), ARRAY_FILTER_USE_KEY);
        }
        if($instance->timestamps) {
            $data[self::CREATED_AT] = date('Y-m-d H:i:s');
        }
        $columns = implode(',', array_keys($data));
        $values = ':'.implode(',:', array_keys($data));
        $sql = "INSERT INTO " . $table . " ({$columns}) VALUES ({$values})";
        $statement = self::$pdo->prepare($sql);
        foreach ($data as $key => $value) {
            $statement->bindValue(":$key", $value);
        }
        return $statement->execute();
    }

    public static function update($id, array $data) : bool
    {
        $instance = new static();
        $table = $instance->guessTableName($instance);
        if($instance->fillable) {
            $data = array_filter($data, fn($key) => in_array($key, $instance->fillable), ARRAY_FILTER_USE_KEY);
        }
        $fields = '';
        if($instance->timestamps) {
            $data[self::UPDATED_AT] = date('Y-m-d H:i:s');
        }
        foreach ($data as $column => $value) {
            $fields .= "{$column} = :{$column},";
        }
        $fields = rtrim($fields, ',');
        $sql = "UPDATE {$table} SET {$fields} WHERE {$instance->primaryKey} = :id";
        $data['id'] = $id;
        $statement = self::$pdo->prepare($sql);
        foreach ($data as $key => $value) {
            $statement->bindValue(":$key", $value);
        }
        return $statement->execute($data);
    }

    public static function delete($id) : bool
    {
        $instance = new static();
        $table = $instance->guessTableName($instance);
        $sql = "DELETE FROM {$table} WHERE {$instance->primaryKey} = :id";
        $statement = self::$pdo->prepare($sql);
        $statement->bindValue(':id', $id);
        return $statement->execute();
    }

    public function save() : bool
    {
        if(!$this->table) {
            $this->table = $this->guessTableName($this);
        }
        if($this->fillable) {
            $this->attributes = array_filter($this->attributes, fn($key) => in_array($key, $this->fillable), ARRAY_FILTER_USE_KEY);
        }
        if ($this->exists) {
            $params = array_map(fn($item) => "$item = :$item", array_keys($this->attributes));
            $sql = "UPDATE $this->table SET ".implode(',', $params)." WHERE {$this->primaryKey} = :{$this->primaryKey}";
        }
        else {
            $params = array_map(fn($item) => ":$item", array_keys($this->attributes));
            $sql = "INSERT INTO $this->table (".implode(',', array_keys($this->attributes)).") VALUES (".implode(',', $params).")";
        }
        $statement = self::$pdo->prepare($sql);
        foreach ($this->attributes as $attribute => $value) {
            $statement->bindValue(":$attribute", $value);
        }
        var_dump($this);
        exit;
        if ($this->exists) {
            $statement->bindValue(":{$this->primaryKey}", $this->{$this->primaryKey});
        }
        return $statement->execute();
    }

    public static function with(string ...$relations) : array
    {
        $instance = new static();
        $builder = new Builder($instance->table);
        return $builder->with($instance, ...$relations);
    }
    public static function paginate(int $perPage) : array
    {
        $instance = new static();
        if(!$instance->table) {
            $instance->table = strtolower((new \ReflectionClass($instance))->getShortName()) . 's';
        }
        $page = $_GET['page'] ?? 1;
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM " . $instance->table . " LIMIT {$perPage} OFFSET {$offset}";
        $statement = self::$pdo->query($sql);
        $rows =  $statement->fetchAll();
        if($rows)
        {
            $total = self::$pdo->query("SELECT COUNT(*) FROM " . $instance->table)->fetchColumn();
            return [
                'data' => $instance->makeInstances($rows),
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage),
                'total' => $total
            ];
        }
        return [
            'data' => [],
            'current_page' => 1,
            'per_page' => $perPage,
            'last_page' => 1,
            'total' => 0
        ];
    }

    public function is(Model $model) : bool
    {
        if(!$this->table) {
            $this->table = strtolower((new \ReflectionClass($this))->getShortName()) . 's';
        }
        return $this->table === $model->table && $this->{$this->primaryKey} === $model->{$model->primaryKey} && self::$pdo === $model::$pdo;
    }

    public function isNot(Model $model) : bool
    {
        return !$this->is($model);
    }

    private function makeInstances(array $rows) : array
    {
        $instances = [];
        foreach ($rows as $row) {
            $instance = new static();
            foreach ($row as $key => $value) {
                $instance->{$key} = $value;
            }
            $instance->hideHiddenFields($instance);
            $instances[] = $instance->attributes;
        }
        return $instances;
    }
    public function getTable() : string
    {
        return $this->table ?? $this->guessTableName($this);
    }

    public function __get($name) : mixed
    {
/*        if ($name === 'pdo' || in_array($name, $this->hidden)) {
            return null;
        }*/
        if(array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }
        return null;
    }

    public function __set($name, $value) : void
    {
        $this->attributes[$name] = $value;
    }
}