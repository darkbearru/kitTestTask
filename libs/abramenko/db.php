<?php

namespace abramenko;
use mysqli;

class DB
{
	private static $_db 		= false;
	private static $_mysqli 	= false;
    private static $_isError    = false;

    public function __construct (
/*        $host = 'localhost', 
        $user = 'test', 
        $password ='password', 
        $database = 'test' */
    ){
        self::$_db = &$this;
        if (empty (self::$_mysqli)) {
            mysqli_report (MYSQLI_REPORT_OFF);

            $json = implode (file ($_SERVER['DOCUMENT_ROOT']."/assets/connect-info.json"));
            $json = json_decode ($json);
            
            $mysqli = new mysqli ($json->host, $json->user, $json->password, $json->database);
            // $mysqli = new mysqli ($host, $user, $password, $database);

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
        return self::$_mysqli->error_list;
    }

    static function insertID ()
    {
        return self::$_mysqli->insert_id;
    }

    private static function createUsersTable ()
    {
        echo "Creating table<br />";
        self::Query (
            "CREATE TABLE `users` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `login` varchar(50) DEFAULT NULL,
            `password` varchar(100) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `loginidx` (`login`,`password`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );
        self::Query ("DROP TABLE IF EXISTS `sessions`;");
        self::Query (
            "CREATE TABLE `sessions` (
            `id` varchar (50),
            `user_id` int(11),
            `activity` timestamp,
            PRIMARY KEY (`id`),
            KEY `activityidx` (`activity`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );
        self::Query ("DROP TABLE IF EXISTS `posts`;");
        self::Query (
            "CREATE TABLE `posts` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `upid` int(11) DEFAULT '0',
            `name` varchar(200) DEFAULT NULL,
            `description` text,
            `changed` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
        );
    }

    private static function createAdminUser ()
    {
        echo "Create admin user<br />";
        $password = md5("password");
        self::Query ("insert into users (login, password) values ('admin', password('$password'))");
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