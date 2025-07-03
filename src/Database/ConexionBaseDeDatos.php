<?php

class ConexionBaseDeDatos
{
    private static ?PDO $instance = null;
    private string $host;
    private string $dbname;
    private string $user;
    private string $password;
    private string $charset;

    private function __construct()
    {
        $this->host = 'localhost';
        $this->dbname = 'sistema_empleados';
        $this->user = 'root';
        $this->password = '123456';
        $this->charset = 'utf8mb4';

        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            self::$instance = new PDO($dsn, $this->user, $this->password, $options);
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            new self();
        }
        return self::$instance;
    }
}