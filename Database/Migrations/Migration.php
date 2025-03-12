<?php

namespace NovaLite\Database\Migrations;

use NovaLite\Database\Database;
use NovaLite\Database\Query\Connections\MySQL;
use NovaLite\Database\Query\Connections\PostgreSQL;
use NovaLite\Database\Query\QueryBuilderInterface;

class Migration
{
    protected string $table;
    private string $connection;
    protected array $columns = [];
    protected array $indexes = [];
    protected array $uniqueKeys = [];
    protected array $foreignKeys = [];
    protected array $droppedColumns = [];
    protected array $droppedIndexes = [];
    protected array $droppedUniqueKeys = [];
    protected array $renamedColumns = [];
    public function getTable(): string
    {
        return $this->table;
    }
    public function getColumns(): array
    {
        return $this->columns;
    }
    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
    }
    public function getIndexes(): array
    {
        return $this->indexes;
    }
    public function setIndexes(array $indexes): void
    {
        $this->indexes = $indexes;
    }
    public function getUniqueKeys(): array
    {
        return $this->uniqueKeys;
    }
    public function setUniqueKeys(array $uniqueKeys): void
    {
        $this->uniqueKeys = $uniqueKeys;
    }
    public function getForeignKeys(): array
    {
        return $this->foreignKeys;
    }
    public function setForeignKeys(array $foreignKeys): void
    {
        $this->foreignKeys = $foreignKeys;
    }


    public function __construct($table)
    {
        $this->table = $table;
        $this->connection = env('DB_CONNECTION');
    }

    public function id() : void
    {
        if ($this->connection == 'pgsql') {
            $this->appendColumn($this->wrapColumn('id') . " BIGSERIAL PRIMARY KEY");
        } else {
            $this->appendColumn($this->wrapColumn('id') . " BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY");
        }
    }
    public function primary(array|string $columns) : void
    {
        if ($this->connection == 'pgsql') {
            $columns = array_map(fn($column) => "\"$column\"", $columns);
            $this->indexes[] = "PRIMARY KEY (" . implode(", ", $columns) . ")";
        } else {
            $columns = array_map(fn($column) => "`$column`", $columns);
            $this->indexes[] = "PRIMARY KEY (" . implode(", ", $columns) . ")";
        }
    }
    public function string($column, $length = 255) : Column
    {
        $this->appendColumn($this->wrapColumn($column) . " VARCHAR($length) NOT NULL");
        return new Column($this, $column);
    }
    public function tinyInteger($column, $unsigned = false) : Column
    {
        if ($this->connection == 'pgsql') {
            $this->appendColumn($this->wrapColumn($column) . " SMALLINT NOT NULL");

        } else {
            $this->appendColumn($this->wrapColumn($column) . " TINYINT" . ($unsigned ? ' UNSIGNED' : '') . ' NOT NULL');
        }
        return new Column($this, $column);
    }
    public function smallInteger($column, $unsigned = false) : Column
    {
        if($this->connection == 'pgsql'){
            $this->appendColumn($this->wrapColumn($column) . " SMALLINT NOT NULL");
        }
        else{
            $this->appendColumn($this->wrapColumn($column) . " SMALLINT" . ($unsigned ? ' UNSIGNED' : '') . ' NOT NULL');
        }
        return new Column($this, $column);
    }
    public function mediumInteger($column, $unsigned = false) : Column
    {
        if($this->connection == 'pgsql'){
            $this->appendColumn($this->wrapColumn($column) . " INTEGER NOT NULL");
        }
        else{
            $this->appendColumn($this->wrapColumn($column) . " MEDIUMINT" . ($unsigned ? ' UNSIGNED' : '') . ' NOT NULL');
        }
        return new Column($this, $column);
    }
    public function integer($column, $unsigned = false) : Column
    {
        if($this->connection == 'pgsql'){
            $this->appendColumn($this->wrapColumn($column) . " INTEGER NOT NULL");
        }
        else{
            $this->appendColumn($this->wrapColumn($column) . " INT" . ($unsigned ? ' UNSIGNED' : '') . ' NOT NULL');
        }
        return new Column($this, $column);
    }
    public function bigInteger($column, $unsigned = false) : Column
    {
        if($this->connection == 'pgsql'){
            $this->appendColumn($this->wrapColumn($column) . " BIGINT NOT NULL");
        }
        else{
            $this->appendColumn($this->wrapColumn($column) . " BIGINT" . ($unsigned ? ' UNSIGNED' : '') . ' NOT NULL');
        }
        return new Column($this, $column);
    }
    public function decimal($column, $length = 8, $decimals = 2, $unsigned = false) : Column
    {
        if($this->connection == 'pgsql'){
            $this->appendColumn($this->wrapColumn($column) . " NUMERIC($length, $decimals) NOT NULL");
        }
        else{
            $this->appendColumn($this->wrapColumn($column) . " DECIMAL($length, $decimals)" . ($unsigned ? ' UNSIGNED' : '') . ' NOT NULL');
        }
        return new Column($this, $column);
    }
    public function float($column, $length = 8, $decimals = 2, $unsigned = false) : Column
    {
        if($this->connection == 'pgsql'){
            $this->appendColumn($this->wrapColumn($column) . " REAL NOT NULL");
        }
        else{
            $this->appendColumn($this->wrapColumn($column) . "  FLOAT($length, $decimals)" . ($unsigned ? ' UNSIGNED' : '') . ' NOT NULL');
        }
        return new Column($this, $column);
    }
    public function double($column, $length = 8, $decimals = 2, $unsigned = false) : Column
    {
        if ($this->connection == 'pgsql') {
            $this->appendColumn($this->wrapColumn($column) . " DOUBLE PRECISION NOT NULL");
        } else {
            $this->appendColumn($this->wrapColumn($column) . " DOUBLE" . ($length ? "($length, $decimals)" : '') . ($unsigned ? ' UNSIGNED' : '') . ' NOT NULL');
        }
        return new Column($this, $column);
    }
    public function char($column, $length = null) : Column
    {
        $this->appendColumn($this->wrapColumn($column) . " CHAR($length) NOT NULL");
        return new Column($this, $column);
    }
    public function tinyText($column) : Column
    {
        if($this->connection == 'pgsql'){
            $this->appendColumn($this->wrapColumn($column) . " TEXT NOT NULL");
        }
        else{
            $this->appendColumn($this->wrapColumn($column) . " TINYTEXT NOT NULL");
        }
        return new Column($this, $column);
    }
    public function text($column) : Column
    {
        $this->appendColumn($this->wrapColumn($column) . " TEXT" . ' NOT NULL');
        return new Column($this, $column);
    }
    public function mediumText($column) : Column
    {
        if($this->connection == 'pgsql'){
            $this->appendColumn($this->wrapColumn($column) . " TEXT NOT NULL");
        }
        else{
            $this->appendColumn($this->wrapColumn($column) . " MEDIUMTEXT NOT NULL");
        }
        return new Column($this, $column);
    }
    public function longText($column) : Column
    {
        if($this->connection == 'pgsql'){
            $this->appendColumn($this->wrapColumn($column) . " TEXT NOT NULL");
        }
        else{
            $this->appendColumn($this->wrapColumn($column) . " LONGTEXT NOT NULL");
        }
        return new Column($this, $column);
    }
    public function date($column) : Column
    {
       $this->appendColumn($this->wrapColumn($column) . " DATE NOT NULL");
        return new Column($this, $column);
    }
    public function dateTime($column) : Column
    {
        if($this->connection == 'pgsql'){
            $this->appendColumn($this->wrapColumn($column) . " TIMESTAMP NOT NULL");
        }
        else{
            $this->appendColumn($this->wrapColumn($column) . " DATETIME NOT NULL");
        }
        return new Column($this, $column);
    }
    public function timestamp($column) : Column
    {
        $this->appendColumn($this->wrapColumn($column) . " TIMESTAMP NOT NULL");
        return new Column($this, $column);
    }
    public function time($column) : Column
    {
        $this->appendColumn($this->wrapColumn($column) . " TIME NOT NULL");
        return new Column($this, $column);
    }
    public function year($column) : Column
    {
        if($this->connection == 'pgsql'){
            $this->appendColumn($this->wrapColumn($column) . " SMALLINT NOT NULL");
        }
        else{
            $this->appendColumn($this->wrapColumn($column) . " YEAR NOT NULL");
        }
        return new Column($this, $column);
    }
    public function boolean($column): Column
    {
        if ($this->connection == 'pgsql') {
            $this->columns[] = "\"$column\" BOOLEAN NOT NULL";
        } else {
            $this->columns[] = "`$column` TINYINT(1) NOT NULL";
        }
        return new Column($this, $column);
    }
    public function foreignId($column) : ForeignIdColumn
    {
        if($this->connection == 'pgsql'){
            $this->appendColumn($this->wrapColumn($column) . " BIGINT");
        }
        else{
            $this->appendColumn($this->wrapColumn($column) . " BIGINT UNSIGNED");
        }
    return new ForeignIdColumn($this, $column, $this->connection);
    }
    public function index(array|string $columns, $name = null) : void
    {
        $indexString = '';
        if ($this->connection == 'mysql') {
            if (is_string($columns)) {
                $name = $name ?? "idx_{$this->table}_{$columns}";
                $indexString = "`$columns`";
            }
            if (is_array($columns)) {
                $name = $name ?? "idx_{$this->table}_" . implode('_', $columns);
                $columns = array_map(fn($column) => "`$column`", $columns);
                $indexString = implode(", ", $columns);
            }
        }
        else if($this->connection == 'pgsql'){
            if (is_string($columns)) {
                $name = $name ?? "idx_{$this->table}_{$columns}";
                $indexString = "\"$columns\"";
            }
            if (is_array($columns)) {
                $name = $name ?? "idx_{$this->table}_" . implode('_', $columns);
                $columns = array_map(fn($column) => "\"$column\"", $columns);
                $indexString = implode(", ", $columns);
            }
        }
        $uniqueExists = false;
        foreach ($this->uniqueKeys as $uniqueKey) {
            if (str_contains($uniqueKey, $indexString)) {
                $uniqueExists = true;
                break;
            }
        }
        if (!$uniqueExists) {
            $exists = false;
            foreach ($this->indexes as $index) {
                if (str_contains($index, $indexString)) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                if($this->connection == 'pgsql'){
                    $this->indexes[] = "CREATE INDEX \"$name\" ON \"{$this->table}\" ($indexString)";
                }
                else{
                    $this->indexes[] = "INDEX `$name` ($indexString)";
                }
            }
        }
    }
    public function unique(array|string $columns, $name = null) : void
    {
        $indexString = '';
        if($this->connection == 'mysql'){
            if(is_string($columns)){
                $name = $name ?? "idx_{$this->table}_{$columns}";
                $indexString = "`$columns`";
            }
            if(is_array($columns)){
                $name = $name ?? "idx_{$this->table}_" . implode('_', $columns);
                $columns = array_map(fn($column) => "`$column`", $columns);
                $indexString = implode(", ", $columns);
            }
        }
        else if($this->connection == 'pgsql'){
            if (is_string($columns)) {
                $name = $name ?? "uniq_{$this->table}_{$columns}";
                $indexString = "\"$columns\"";
            }
            if (is_array($columns)) {
                $name = $name ?? "uniq_{$this->table}_" . implode('_', $columns);
                $columns = array_map(fn($column) => "\"$column\"", $columns);
                $indexString = implode(", ", $columns);
            }
        }

        $commonIndexExists = false;
        $keyNumber = 0;
        foreach ($this->indexes as $position => $index) {
            if (str_contains($index, $indexString)) {
                $commonIndexExists = true;
                $keyNumber = $position;
                break;
            }
        }
        if ($commonIndexExists) {
            unset($this->indexes[$keyNumber]);
        }
        $exists = false;
        foreach ($this->uniqueKeys as $uniqueKey) {
            if (str_contains($uniqueKey, $indexString)) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            if($this->connection == 'pgsql'){
                $this->uniqueKeys[] = "CREATE UNIQUE INDEX \"$name\" ON \"{$this->table}\" ($indexString)";
            }
            else{
                $this->uniqueKeys[] = "UNIQUE `$name` ($indexString)";
            }
        }
    }
    public function timestamps() : void
    {
        $this->appendColumn($this->wrapColumn('created_at') . " TIMESTAMP NULL DEFAULT NULL");
        $this->appendColumn($this->wrapColumn('updated_at') . " TIMESTAMP NULL DEFAULT NULL");
    }
    public function dropColumn(array|string $columns) : void
    {
        if(is_string($columns)){
            $columns = [$columns];
        }
        $this->droppedColumns = array_merge($this->droppedColumns, $columns);
    }
    public function dropIndex($name) : void
    {
        $this->droppedIndexes[] = $name;
    }
    public function dropUnique($name) : void
    {
        $this->droppedUniqueKeys[] = $name;
    }
    public function renameColumn($oldName, $newName) : void
    {
        $this->renamedColumns[count($this->renamedColumns)]['oldName'] = $oldName;
        $this->renamedColumns[count($this->renamedColumns) - 1]['newName'] = $newName;
    }
    public function renameIndex($oldName, $newName) : void
    {
        $this->dropIndex($oldName);
        if($this->connection == 'mysql'){
            $stmt = Database::getInstance()->prepare("SHOW INDEX FROM `$this->table` WHERE Key_name = :keyName");
            $stmt->bindParam(':keyName', $oldName);
            $stmt->execute();

            $index = $stmt->fetch(\PDO::FETCH_ASSOC);

            $columnName = $index['Column_name'];
            $isUnique = $index['Non_unique'] == 0;
        }
        else if($this->connection == 'pgsql'){
            $stmt = Database::getInstance()->prepare("
                SELECT a.attname AS column_name,
                       i.indisunique AS is_unique
                FROM pg_index AS i
                JOIN pg_class AS c ON c.oid = i.indexrelid
                JOIN pg_class AS t ON t.oid = i.indrelid
                JOIN pg_attribute AS a ON a.attnum = ANY(i.indkey)
                WHERE c.relname = :keyName
                AND t.relname = :tableName
            ");
            $stmt->bindParam(':keyName', $oldName);
            $stmt->bindParam(':tableName', $this->table);
            $stmt->execute();

            $index = $stmt->fetch(\PDO::FETCH_ASSOC);

            $columnName = $index['column_name'];
            $isUnique = $index['is_unique'];
        }
        if ($isUnique) {
            $this->unique($columnName, $newName);
        } else {
            $this->index($columnName, $newName);
        }
    }
    public function build(string $migrationType) : array|string
    {
        if($this->connection == 'mysql'){
            return $this->mysqlbuild($migrationType);
        }
        else if($this->connection == 'pgsql'){
            return $this->pgsqlbuild($migrationType);
        }
        else{
            return "Unsupported database type";
        }
    }
    private function wrapColumn($column) : string
    {
        return $this->connection == 'pgsql' ? "\"$column\"" : "`$column`";
    }
    private function appendColumn($definition) : void
    {
        $this->columns[] = $definition;
    }
    private function mysqlbuild(string $migrationType) : array|string
    {
        $columns = implode(", ", $this->columns);
        $indexes = implode(", ", $this->indexes);
        $uniqueKeys = implode(", ", $this->uniqueKeys);
        $foreignKeys = implode(", ", $this->foreignKeys);

        if ($migrationType == 'create') {
            return $this->buildCreateTable('`');
        } else {
            $queries = [];
            $query = "ALTER TABLE `$this->table` ";
            if (!empty($this->droppedColumns)) {
                $query .= "DROP COLUMN " . implode(", DROP COLUMN ", $this->droppedColumns);
            }
            if (!empty($this->droppedIndexes)) {
                if (!empty($this->droppedColumns)) {
                    $query .= ", DROP INDEX " . implode(", DROP INDEX ", $this->droppedIndexes);
                } else {
                    $query .= "DROP INDEX " . implode(", DROP INDEX ", $this->droppedIndexes);
                }
            }
            if (!empty($this->droppedUniqueKeys)) {
                if (!empty($this->droppedColumns) || !empty($this->droppedIndexes)) {
                    $query .= ", DROP INDEX " . implode(", DROP INDEX ", $this->droppedUniqueKeys);
                } else {
                    $query .= "DROP INDEX " . implode(", DROP INDEX ", $this->droppedUniqueKeys);
                }
            }
            if (!empty($columns)) {
                if (!empty($this->droppedColumns) || !empty($this->droppedIndexes) || !empty($this->droppedUniqueKeys)) {
                    $query .= ", ADD COLUMN $columns";
                } else {
                    $query .= "ADD COLUMN $columns";
                }
            }
            if (!empty($indexes)) {
                if (!empty($columns) || !empty($this->droppedIndexes) || !empty($this->droppedUniqueKeys) || !empty($this->droppedColumns)) {
                    $query .= ", ADD $indexes";
                } else {
                    $query .= "ADD $indexes";
                }
            }
            if (!empty($uniqueKeys)) {
                if (!empty($columns) || !empty($indexes) || !empty($this->droppedIndexes) || !empty
                    ($this->droppedUniqueKeys) || !empty($this->droppedColumns)) {
                    $query .= ", ADD $uniqueKeys";
                } else {
                    $query .= "ADD $uniqueKeys";
                }
            }
            if (!empty($foreignKeys)) {
                if (!empty($columns) || !empty($indexes) || !empty($uniqueKeys) || !empty($this->droppedIndexes) ||
                    !empty($this->droppedUniqueKeys) || !empty($this->droppedColumns)) {
                    $query .= ", ADD $foreignKeys";
                } else {
                    $query .= "ADD $foreignKeys";
                }
            }

            if (!empty($columns) || !empty($indexes) || !empty($uniqueKeys) || !empty($foreignKeys) || !empty($this->droppedColumns) || !empty($this->droppedIndexes) || !empty($this->droppedUniqueKeys)) {
                $query .= ";";
                $queries[] = $query;
            }
        }
        if (!empty($this->renamedColumns)) {
            foreach ($this->renamedColumns as $renamedColumn) {
                $stmt = Database::getInstance()->query("SHOW COLUMNS FROM $this->table LIKE '{$renamedColumn['oldName']}'");
                $column = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($column) {
                    $columnType = $column['Type'];
                    $isNullable = $column['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
                    $default = $column['Default'] ? "DEFAULT '{$column['Default']}'" : '';
                }

                if (!empty($columns) || !empty($indexes) || !empty($uniqueKeys) || !empty($foreignKeys) || !empty($this->droppedColumns) || !empty($this->droppedIndexes) || !empty($this->droppedUniqueKeys)) {
                    $queries[] = "ALTER TABLE `$this->table` CHANGE `{$renamedColumn['oldName']}` `{$renamedColumn['newName']}` $columnType $isNullable $default;";
                } else {
                    $queries[] = "CHANGE `{$renamedColumn['oldName']}` `{$renamedColumn['newName']}` $columnType $isNullable $default;";
                }
            }
        }
        return $queries;
    }
    private function pgsqlbuild(string $migrationType): array|string
    {
        $columns = implode(", ", $this->columns);
        $indexes = implode(", ", $this->indexes);
        $uniqueKeys = implode(", ", $this->uniqueKeys);
        $foreignKeys = implode(", ", $this->foreignKeys);

        if ($migrationType === 'create') {
            return $this->buildCreateTable('"');
        } else {
            $queries = [];
            $query = "ALTER TABLE \"$this->table\" ";

            if (!empty($this->droppedColumns)) {
                $query .= "DROP COLUMN " . implode(", DROP COLUMN ", array_map(fn($col) => "\"$col\"", $this->droppedColumns));
            }
            if (!empty($this->droppedIndexes)) {
                $queries[] = implode(";\n", array_map(fn($index) => "DROP INDEX IF EXISTS \"$index\"", $this->droppedIndexes)) . ";";
            }

            if (!empty($this->droppedUniqueKeys)) {
                $queries[] = implode(";\n", array_map(fn($key) => "DROP INDEX IF EXISTS \"$key\"", $this->droppedUniqueKeys)) . ";";
            }

            if (!empty($columns)) {
                $query .= (!empty($this->droppedColumns) ? ", " : "") . "ADD COLUMN $columns";
            }
            if (!empty($indexes)) {
                $query .= (!empty($columns) || !empty($this->droppedColumns) ? ", " : "") . "ADD $indexes";
            }
            if (!empty($uniqueKeys)) {
                $query .= (!empty($columns) || !empty($indexes) || !empty($this->droppedColumns) ? ", " : "") . "ADD $uniqueKeys";
            }
            if (!empty($foreignKeys)) {
                $query .= (!empty($columns) || !empty($indexes) || !empty($uniqueKeys) || !empty($this->droppedColumns) ? ", " : "") . "ADD $foreignKeys";
            }

            if (trim($query) !== "ALTER TABLE \"$this->table\"") {
                $queries[] = $query . ";";
            }

            if (!empty($this->renamedColumns)) {
                foreach ($this->renamedColumns as $renamedColumn) {
                    $queries[] = "ALTER TABLE \"$this->table\" RENAME COLUMN \"{$renamedColumn['oldName']}\" TO \"{$renamedColumn['newName']}\";";
                }
            }

            return $queries;
        }
    }
    private function buildCreateTable(string $quote): string
    {
        $columns = implode(", ", $this->columns);
        $indexes = implode(", ", $this->indexes);
        $uniqueKeys = implode(", ", $this->uniqueKeys);
        $foreignKeys = implode(", ", $this->foreignKeys);

        $query = "CREATE TABLE {$quote}{$this->table}{$quote} ($columns";

        if (!empty($indexes)) {
            $query .= ", $indexes";
        }
        if (!empty($uniqueKeys)) {
            $query .= ", $uniqueKeys";
        }
        if (!empty($foreignKeys)) {
            $query .= ", $foreignKeys";
        }

        $query .= ");\n";

        return $query;
    }
}