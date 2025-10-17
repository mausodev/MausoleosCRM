<?php
class Database {
    private static $instance = null;
    private $connection;

    /**
     * Constructor privado - Patrón Singleton
     * No se puede instanciar desde fuera de la clase
     */

    private function __construct(){
        $config = require __DIR__ . '/../config/database.php';

        try {
            $this->connection = new PDO(
                "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}",
                $config['username'],
                $config['password'],
                $config['options']
                );
        } catch (PDOException $e) {
            error_log("Error en la conexion a la base de datos: " . $e->getMessage());
            die("Error de conexion: " . $e->getMessage());
        }
    }

    public static function getInstance(){
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(){
        return $this->connection;
    }

    private function __clone() {}
    public function __wakeup(){
        throw new Exception("No se puede deserealizar singleton");
    }
}