<?php

namespace NovaLite\Database\Migrations;

class ForeignKeyDefinition
{
    protected Migration $table;
    protected string $column;
    public function __construct($table, $column)
    {
        $this->table = $table;
        $this->column = $column;
    }

    public function onDelete($action = 'RESTRICT') : self
    {
        $foreignKeys = $this->table->getForeignKeys();
        $foreignPosition = 0;
        foreach ($foreignKeys as $key => $foreignKey) {
            if (str_contains($foreignKey, "FOREIGN KEY (`$this->column`)")) {
                $foreignPosition = $key;
            }
        }
        $action = strtoupper($action);
        $foreignKeys[$foreignPosition] = "$foreignKeys[$foreignPosition] ON DELETE $action";
        $this->table->setForeignKeys($foreignKeys);
        return $this;
    }
    public function onUpdate($action = 'RESTRICT') : self
    {
        $foreignKeys = $this->table->getForeignKeys();
        $foreignPosition = 0;
        foreach ($foreignKeys as $key => $foreignKey) {
            if (str_contains($foreignKey, "FOREIGN KEY (`$this->column`)")) {
                $foreignPosition = $key;
            }
        }
        $action = strtoupper($action);
        $foreignKeys[$foreignPosition] = "$foreignKeys[$foreignPosition] ON UPDATE $action";
        $this->table->setForeignKeys($foreignKeys);
        return $this;
    }
}