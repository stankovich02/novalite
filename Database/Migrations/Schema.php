<?php

namespace NovaLite\Database\Migrations;

use NovaLite\Application;

class Schema
{
    public static function create($table, callable $callback) : void
    {
        $migration = new Migration($table);

        $callback($migration);

        $query = $migration->build('create');

        Application::$app->db->exec($query);
    }

    public static function modify($table, callable $callback) : void
    {
        $migration = new Migration($table);

        $callback($migration);

        $queries = $migration->build('modify');

        foreach ($queries as $query) {
           if ($query) {
               Application::$app->db->exec($query);
           }
        }
    }

    public static function drop($table) : void
    {
        Application::$app->db->exec("DROP TABLE IF EXISTS `$table`;");
    }
}