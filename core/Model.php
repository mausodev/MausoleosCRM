<?php
require_once __DIR__ . '/Database.php';

/**
 * Clase Model Base
 * 
 * Todos los modelos heredarán de esta clase.
 * Proporciona métodos comunes para interactuar con la base de datos.
 */
class Model {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Ejecutar una consulta preparada
     * 
     * @param string $sql - Consulta SQL con placeholders
     * @param array $params - Parámetros para la consulta
     * @return PDOStatement
     */
    protected function query($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en query: " . $e->getMessage());
            throw new Exception("Error al ejecutar la consulta");
        }
    }
    
    /**
     * Obtener todos los registros
     * 
     * @return array
     */
    public function all() {
        $sql = "SELECT * FROM {$this->table}";
        return $this->query($sql)->fetchAll();
    }
    
    /**
     * Buscar por ID
     * 
     * @param int $id
     * @return array|false
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        return $this->query($sql, [':id' => $id])->fetch();
    }
    
    /**
     * Buscar con condiciones WHERE
     * 
     * Ejemplo: $model->where(['puesto' => 'COORDINADOR', 'sucursal' => 'Norte'])
     * 
     * @param array $conditions - ['columna' => 'valor']
     * @return array
     */
    public function where($conditions) {
        if (empty($conditions)) {
            return $this->all();
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE ";
        $clauses = [];
        $params = [];
        
        foreach ($conditions as $key => $value) {
            $clauses[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        
        $sql .= implode(' AND ', $clauses);
        return $this->query($sql, $params)->fetchAll();
    }
    
    /**
     * Buscar el primer registro con condiciones
     * 
     * @param array $conditions
     * @return array|false
     */
    public function first($conditions) {
        $results = $this->where($conditions);
        return !empty($results) ? $results[0] : false;
    }
    
    /**
     * Insertar un nuevo registro
     * 
     * @param array $data - ['columna' => 'valor']
     * @return int - ID del registro insertado
     */
    public function insert($data) {
        $columns = array_keys($data);
        $placeholders = array_map(function($col) {
            return ":$col";
        }, $columns);
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $params = [];
        foreach ($data as $key => $value) {
            $params[":$key"] = $value;
        }
        
        $this->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualizar un registro por ID
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $setParts = [];
        $params = [':id' => $id];
        
        foreach ($data as $key => $value) {
            $setParts[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE id = :id",
            $this->table,
            implode(', ', $setParts)
        );
        
        $this->query($sql, $params);
        return true;
    }
    
    /**
     * Eliminar un registro por ID
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $this->query($sql, [':id' => $id]);
        return true;
    }
    
    /**
     * Contar registros con condiciones opcionales
     * 
     * @param array $conditions
     * @return int
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $sql .= " WHERE ";
            $clauses = [];
            
            foreach ($conditions as $key => $value) {
                $clauses[] = "$key = :$key";
                $params[":$key"] = $value;
            }
            
            $sql .= implode(' AND ', $clauses);
        }
        
        $result = $this->query($sql, $params)->fetch();
        return (int)$result['total'];
    }
}