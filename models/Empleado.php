<?php
require_once __DIR__ . '/../core/Model.php';

class Cliente extends Model{
    protected $table = 'empleado';

    public function getCoordinadores($sucursal){
        $sql = "SELECT id, correo, iniciales, puesto
                FROM {$this->table}
                WHERE puesto = :puesto
                AND sucursal = :sucursal
                ORDER BY correo asc";
        return $this->query($sql,[
            ':puesto' => 'COORDINADOR',
            ':sucursal'=> $sucursal
        ])->fetchAll();
    }

    public function getAsesoresByCoordinador($coordinadorId, $mes = null){
        $sql = "SELECT 
                e.id,
                e.correo,
                e.iniciales,
                e.puesto,
                COALESCE(m.meta, 0) as meta
                FROM {$this->table} e
                LEFT JOIN meta_venta m on e.id = m.id_asesor" ;
        $params = [':coordinador_id'=> $coordinadorId];

        if ($mes !== null) {
            $sql .= " AND m.nombre_mes = :mes ";
            $params[':mes'] = $mes;
        }

        $sql .= "WHERE e.id_supervisor = :coordinador_id
                 AND e.puesto = 'ASESOR'
                 ORDER BY e.correo ASC";
        return $this->query($sql, $params)->fetchAll();
    }

    public function getMetaCoordinador($coordinadorId, $mes) {
        $sql = "SELECT COALESCE(SUM(m.meta), 0) as total
                FROM {$this->table} e
                INNER JOIN meta_venta m ON e.id = m.id_asesor
                WHERE e.id_supervisor = :coordinador_id
                AND m.nombre_mes = :mes
                AND e.puesto = 'ASESOR'";
        
        $result = $this->query($sql, [
            ':coordinador_id' => $coordinadorId,
            ':mes' => $mes
        ])->fetch();
        
        return (float)($result['total'] ?? 0);
    }

    public function getMetaAsesor($asesorId, $mes) {
        $sql = "SELECT COALESCE(meta, 0) as meta
                FROM meta_venta
                WHERE id_asesor = :asesor_id
                AND nombre_mes = :mes
                LIMIT 1";
        
        $result = $this->query($sql, [
            ':asesor_id' => $asesorId,
            ':mes' => $mes
        ])->fetch();
        
        return (float)($result['meta'] ?? 0);
    }

    public function getByEmail($email) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE correo = :correo 
                LIMIT 1";
        
        return $this->query($sql, [':correo' => $email])->fetch();
    }

    public function esCoordinador($empleadoId){
        $sql = "SELECT puesto from {$this->table}
        WHERE id = :id LIMIT 1";

        $result = $this->query($sql, [':id',$empleadoId]) -> fetch();

        return $result && $result['puesto'] === 'COORDINADOR';
    }

    public function getAsesoresBySucursal($sucursal) {
        $sql = "SELECT id, correo, iniciales, id_supervisor
                FROM {$this->table}
                WHERE puesto = 'ASESOR'
                AND sucursal = :sucursal
                ORDER BY correo ASC";
        
        return $this->query($sql, [':sucursal' => $sucursal])->fetchAll();
    }
       
}
