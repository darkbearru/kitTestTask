<?php

namespace abramenko;
use mysqli;

class DB
{
	private static $_db 		= false;
	private static $_mysqli 	= false;
    private static $_isError    = false;

    public function __construct (
        $host = 'localhost', 
        $user = 'test', 
        $password ='password', 
        $database = 'test'
    ){
        self::$_db = &$this;
        if (empty (self::$_mysqli)) {
            mysqli_report (MYSQLI_REPORT_OFF);

            $mysqli = new mysqli ($host, $user, $password, $database);

            if ($mysqli->connect_error)
                throw new Exception("Connect failed: %s", $mysqli->connect_error);

            self::$_mysqli = &$mysqli;
        }
        return self::$_db;
    }

    /**
     * Выполняем проверку базы, 
     * если нет таблицы users, то создаём её
     * если нет пользователя admin, то добавляем его с паролем "password"
     */
    static function checkDB ()
    {
        $_result = self::Query ("select * from users where login='admin'", true);

        // Если не найдена таблица, то создаём её
        if (self::$_isError) {
            foreach (self::$_mysqli->error_list as $error) {
                if ($error['errno'] === 1146) {
                    self::createUsersTable ();
                    $_result = self::Query ("select * from users", true);
                    break;
                }
            }
        }

        // Если не найден админ, то добавляем его
        if (empty ($_result)) {
            self::createAdminUser ();
        }

    }

    static function Query ($query, $singleRecord = false)
    {
        self::$_isError = false;

        $_result = self::$_mysqli->query ($query);

        if (!empty (self::$_mysqli->error_list)) {
            self::$_isError = true;
            return false;
        } 
        
        $_result = self::getRecords ($_result);
        if (!empty ($_result) && $singleRecord) {
            $_result = $_result[0];
        }

        return $_result;
    }

    static function isError ()
    {
        return self::$_isError;
    }

    static function errorsList ()
    {
        return self::$_mysqli->errors_list;
    }

    private static function createUsersTable ()
    {
        echo "Creating table<br />";
        self::Query (
            "CREATE TABLE `users` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `login` varchar(32) DEFAULT NULL,
            `password` varchar(100) DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci"
        );
    }

    private static function createAdminUser ()
    {
        echo "Create admin user<br />";
        self::Query ("insert into users (login, password) values ('admin', '".md5('password')."')");
    }

    private static function getRecords ($result)
    {
        if (!is_object ($result)) return [];
        if (empty ($result->num_rows)) return [];

        $_result = [];
        while($record = $result->fetch_object()) { 
            $_result[] = $record;
        }

        return $_result;

    }
}