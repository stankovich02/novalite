<?php

namespace NovaLite\Database\Migrations;

use NovaLite\Database\Database;

class Column
{
    protected Migration $table;

    protected string $column;
    public function __construct(Migration $table, $column)
    {
        $this->table = $table;
        $this->column = $column;
    }
    public function nullable() : self
    {
        $columns = $this->table->getColumns();
        $lastColumn = end($columns);
        if(str_contains($lastColumn, 'NOT NULL')) {
            if(env('DB_CONNECTION') == 'mysql'){
                $nullableColumn = preg_replace('/NOT NULL/', 'NULL', $lastColumn);
            }
            else{
                $nullableColumn = preg_replace('/NOT NULL/', '', $lastColumn);
            }
            $columnsLength = count($columns);
            $columns[$columnsLength - 1] = $nullableColumn;

            $this->table->setColumns($columns);

        }

        return $this;
    }
    public function default($value) : self
    {
        $columns = $this->table->getColumns();
        $lastColumn = end($columns);
        if(!str_contains($lastColumn, 'DEFAULT')) {
            if ($value === null) {
                $defaultValue = 'NULL';
            } else if (strtoupper($value) === 'CURRENT_TIMESTAMP') {
                $defaultValue = $value;
            } else {
                $defaultValue = "'$value'";
            }

            if(env('DB_CONNECTION') == 'mysql'){
                $defaultColumn = str_contains($lastColumn, 'NOT NULL')
                    ? preg_replace('/NOT NULL/', "NULL DEFAULT $defaultValue", $lastColumn)
                    : $lastColumn . " DEFAULT $defaultValue";
            }
            else{
                $defaultColumn = $lastColumn . " DEFAULT $defaultValue";
            }


            $columns[count($columns) - 1] = $defaultColumn;
            $this->table->setColumns($columns);
        }

        return $this;
    }
    public function index($name = null) : self
    {
        $columns = $this->table->getColumns();
        $indexes = $this->table->getIndexes();
        $name = $name ?? "idx_{$this->table->getTable()}_{$this->column}";

        $lastColumn = end($columns);
        $columnName = preg_match('/`(\w+)`/', $lastColumn, $matches) ? $matches[1] : null;

        if ($columnName && !$this->isIndexedOrUnique($columnName, $indexes)) {
            if(env('DB_CONNECTION') == 'mysql'){
                $indexes[] = "INDEX `$name` (`$columnName`)";
            }
            else{
                $indexes[] = "CREATE INDEX $name ON {$this->table->getTable()} ($columnName)";
            }
            $this->table->setIndexes($indexes);
        }

        return $this;
    }
    private function isIndexedOrUnique($columnName, $indexes) : bool
    {
        foreach ($indexes as $index) {
            if (str_contains($index, $columnName)) {
                return true;
            }
        }
        return false;
    }
    public function unique($name = null) : self
    {
        $columns = $this->table->getColumns();
        $uniqueKeys = $this->table->getUniqueKeys();
        $indexes = $this->table->getIndexes();
        $name = $name ?? "idx_{$this->table->getTable()}_{$this->column}";

        $lastColumn = end($columns);
        $columnName = preg_match('/`(\w+)`/', $lastColumn, $matches) ? $matches[1] : null;

        if ($columnName) {
            $this->removeCommonIndexes($columnName, $indexes);
            $this->addUniqueKey($columnName, $uniqueKeys, $name);
        }

        return $this;
    }
    private function removeCommonIndexes($columnName, &$indexes) : void
    {
        foreach ($indexes as $key => $index) {
            if (str_contains($index, $columnName)) {
                unset($indexes[$key]);
                break;
            }
        }
        $this->table->setIndexes($indexes);
    }
    private function addUniqueKey($columnName, &$uniqueKeys, $name) : void
    {
        foreach ($uniqueKeys as $uniqueKey) {
            if (str_contains($uniqueKey, $columnName)) {
                return;
            }
        }
        if(env('DB_CONNECTION') == 'mysql'){
            $uniqueKeys[] = "UNIQUE `$name` (`$columnName`)";
        }
        else{
            $uniqueKeys[] = "CONSTRAINT {$name} UNIQUE ($columnName)";
        }
        $this->table->setUniqueKeys($uniqueKeys);
    }
    public function after($column) : self
    {
        if(env('DB_CONNECTION') == 'pgsql'){
            throw new \Exception("PostgreSQL does not support the 'AFTER' clause for column position changes.");
        }
        $columns = $this->table->getColumns();
        $lastColumn = end($columns);
        if(!str_contains($lastColumn, 'AFTER')) {
            $columns[count($columns) - 1] = "$lastColumn AFTER `$column`";
            $this->table->setColumns($columns);
        }

        return $this;
    }
    public function change() : void
    {
        $columns = $this->table->getColumns();
        $lastColumn = end($columns);
        $modifiedColumn = "MODIFY COLUMN $lastColumn";
        $columns[count($columns) - 1] = $modifiedColumn;
        $this->table->setColumns($columns);
    }
}