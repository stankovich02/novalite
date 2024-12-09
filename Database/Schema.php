<?php

namespace NovaLite\Database;

use NovaLite\Application;

class Schema
{
    public static function getTables()
    {
        $tables = Database::statement('SHOW TABLES')->fetchAll();
        return array_map(fn($table) => $table->{array_key_first((array)$table)}, $tables);
    }
    public static function getViews()
    {
        $views = Database::statement('SHOW FULL TABLES WHERE TABLE_TYPE LIKE "VIEW"')->fetchAll();
        return array_map(fn($view) => $view->{array_key_first((array)$view)}, $views);
    }
    public static function getColumns(string $table)
    {
        $columns = Database::statement("SHOW COLUMNS FROM $table")->fetchAll();
        return array_map(fn($column) => $column->Field, $columns);
    }
    public static function getColumnsData(string $table) : array
    {
        $columns = Database::statement("SHOW COLUMNS FROM $table")->fetchAll();
        //make an array with keys field name,type,max_length,nullable,default,primary
        return array_map(fn($column) => ['field' => $column->Field, 'type' => $column->Type, 'nullable' => $column->Null, 'default' => $column->Default, 'primary' => $column->Key === 'PRI'], $columns);
    }
    public static function getPrimaryKey(string $table) : string
    {
        $primaryKey = Database::statement("SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'")->fetch();
        return $primaryKey->Column_name;
    }
    public static function getIndexes(string $table) : array
    {
        $indexes = Database::statement("SHOW INDEX FROM $table WHERE Key_name != 'PRIMARY'")->fetchAll();
        return array_map(fn($index) => ['column' => $index->Column_name,'name' => $index->Key_name], $indexes);
    }
    public static function getForeignKeys(string $table) : array
    {
        $foreignKeys = Database::statement("SELECT COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = '$table' AND TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL")->fetchAll();
        return array_map(fn($foreignKey) => ['column' => $foreignKey->COLUMN_NAME, 'name' => $foreignKey->CONSTRAINT_NAME, 'referenced_table' => $foreignKey->REFERENCED_TABLE_NAME, 'referenced_column' => $foreignKey->REFERENCED_COLUMN_NAME], $foreignKeys);
    }
    public static function tableExists(string $table) : bool
    {
        return in_array($table, self::getTables());
    }
    public static function fieldExists(string $table, string $field) : bool
    {
        return in_array($field, self::getColumns($table));
    }
}