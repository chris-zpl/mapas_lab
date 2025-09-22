<?php
class Modelos
{
    protected $db;
    public function __construct(Database $db)
    {
        $this->db = $db;
    }
    public function get($info)
    {
        $sql = "SELECT * FROM mapas_lab_modelos WHERE id=:id;";
        return $this->db->runSQL($sql, [':id' => $info])->fetch();
    }
    public function getPerSalaModelo($sala)
    {
        $sql = "SELECT * FROM mapas_lab_modelos WHERE sala=:sala ORDER BY id ASC;";
        return $this->db->runSQL($sql, [':sala' => $sala])->fetchAll();
    }
    public function getModificado($sala)
    {
        $sql = "SELECT modificado FROM mapas_lab_modelos WHERE sala=:sala ORDER BY modificado DESC LIMIT 1;";
        return $this->db->runSQL($sql, [':sala' => $sala])->fetch();
    }
    public function getLogs($sala)
    {
        try {
            $sql = "SELECT * FROM mapas_lab_modelos WHERE sala = :sala ORDER BY id ASC";
            return $this->db->runSQL($sql, [':sala' => $sala])->fetchAll();
        } catch (PDOException $e) {
            throw $e;
        }
    }
    public function insert($infos)
    {
        try {
            $sql = "INSERT INTO mapas_lab_modelos(titulo, descricao, sala, log, modificado, mostrar) VALUES (:titulo,:descricao,:sala,:log,:modificado,:mostrar);";
            $this->db->runSQL($sql, $infos);
            return true;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                return false;
            } else {
                throw $e;
            }
        }
    }
    public function update($infos)
    {
        try {
            $colunas = array_keys($infos);
            $setCampos = [];
            foreach ($colunas as $col) {
                if ($col !== 'id') {
                    $setCampos[] = "$col = :$col";
                }
            }
            $sql = "UPDATE mapas_lab_modelos SET " . implode(', ', $setCampos) . " WHERE id = :id";
            $this->db->runSQL($sql, $infos);
            return true;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                return false;
            } else {
                throw $e;
            }
        }
    }
    public function updateLog($infos)
    {
        try {
            $sql = "UPDATE mapas_lab_modelos SET log = log || :novoLog WHERE id = :id";
            $this->db->runSQL($sql, $infos);
            return true;
        } catch (PDOException $e) {
            throw $e;
        }
    }
}