<?php
class Database
{
    private static ?mysqli $conn = null;

    public static function getConnection(): mysqli
    {
        if (self::$conn === null) {
            $host = 'localhost';
            $db   = 'bdm-pwci2';
            $user = 'root';
            $pass = '';

            $conn = @new mysqli($host, $user, $pass, $db);
            if ($conn->connect_errno) {
                die('Database connection error: ' . $conn->connect_error);
            }
            // Opcional: set charset
            $conn->set_charset('utf8mb4');
            self::$conn = $conn;
        }
        return self::$conn;
    }
}
