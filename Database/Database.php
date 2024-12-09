<?php

namespace NovaLite\Database;

use NovaLite\Application;
use NovaLite\Database\Query\Builder;
use NovaLite\Database\Query\QueryBuilderInterface;

class Database
{
    private  static ?Database $instance = null;
    private  static \PDO $pdo;
    private function __construct(array $config = null)
    {
       try{
           $default = $config['default'];
           $dbConfig = $config['connections'][$default];
           $dbName = $dbConfig['database'] ?? '';
           $dbType = $dbConfig['type'] ?? '';
           $port = $dbConfig['port'];
           $host = $dbConfig['host'];
           $user = $dbConfig['user'];
           $password = $dbConfig['password'];
           $dsn = "$dbType:host=$host;port=$port;dbname=$dbName";
           self::$pdo = new \PDO($dsn, $user, $password);
           Model::setConnection(self::$pdo);
           self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
           self::$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
       }
         catch(\PDOException $e){
              die('Database connection failed: ' . $e->getMessage());
         }
    }

    public static function getInstance(array $config = []): \PDO
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance::$pdo;
    }
    public static function getConnectionType() : string
    {
        return self::$pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }
    public static function select(string $query, array $bindings = []) : mixed
    {
        $statement = self::$pdo->prepare($query);
        $statement->execute($bindings);
        return $statement->fetchAll();
    }
    public static function table(string $table) : Builder
    {
        return new Builder($table);
    }
    public static function insert(string $query, array $bindings = []) : bool
    {
        $statement = self::$pdo->prepare($query);
        return $statement->execute($bindings);
    }
    public static function update(string $query, array $bindings = []) : bool
    {
        $statement = self::$pdo->prepare($query);
        return $statement->execute($bindings);
    }
    public static function delete(string $query, array $bindings = []) : bool
    {
        $statement = self::$pdo->prepare($query);
        return $statement->execute($bindings);
    }
    public static function transaction(\Closure $callback) : void
    {
        self::$pdo->beginTransaction();
        try {
            $callback();
            self::$pdo->commit();
        } catch (\Exception $e) {
            self::$pdo->rollBack();
            throw $e;
        }
    }
    public static function beginTransaction() : void
    {
        self::$pdo->beginTransaction();
    }
    public static function rollBack() : void
    {
        self::$pdo->rollBack();
    }
    public static function commit() : void
    {
        self::$pdo->commit();
    }
    public static function statement(string $query) : false|\PDOStatement
    {
        return self::$pdo->query($query);
    }
    public static function unprepared(string $query) : false|\PDOStatement
    {
        return self::$pdo->query($query);
    }
}