<?php

namespace App\Core;

class Database
{
    private static $pdo = null;

    public static function getConnection(): \PDO
    {
        if (self::$pdo === null) {
            $config = require __DIR__ . '/../../config/database.php';
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            self::$pdo = new \PDO($dsn, $config['username'], $config['password']);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
            self::$pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
            self::$pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        }
        return self::$pdo;
    }
}
