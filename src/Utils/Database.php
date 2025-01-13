<?php

namespace App\Utils;

use PDO;
use RuntimeException;

class Database
{
    private static $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $host = $_ENV['DB_HOST'];
            $dbname = $_ENV['DB_NAME'];
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASS'];
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 5,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                PDO::MYSQL_ATTR_SSL_CA => $_ENV['DB_CA_CERT'],
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
                PDO::ATTR_PERSISTENT => true
            ];

            // Add connection timeout directly in DSN string
            self::$connection = new PDO(
                "mysql:host={$host};dbname={$dbname};charset=utf8;connect_timeout=10;timeout=30",
                $user,
                $pass,
                $options
            );
        }

        return self::$connection;
    }
}
