<?php

namespace NovaLite\Database\Migrations;

class ForeignIdColumn
{
    protected Migration $table;
    protected string $column;
    private string $connection;
    public function __construct($table, $column, $connection)
    {
        $this->table = $table;
        $this->column = $column;
        $this->connection = $connection;
    }

    public function references($table, $column = 'id') : ForeignKeyDefinition
    {
        $foreignKeys = $this->table->getForeignKeys();
        if($this->connection === 'pgsql') {
            $foreignKeys[] = "FOREIGN KEY (\"{$this->column}\") REFERENCES \"$table\"(\"$column\")";
        } else {
            $foreignKeys[] = "FOREIGN KEY (`{$this->column}`) REFERENCES `$table`(`$column`)";
        }
        $this->table->setForeignKeys($foreignKeys);
        return new ForeignKeyDefinition($this->table, $this->column);
    }
}